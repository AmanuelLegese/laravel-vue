<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Manufacturer;
use App\Models\Status;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PharmacyInventoryTest extends TestCase
{
    use RefreshDatabase;

    private array $ids;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('pharmacist', 'web');
        $this->actAs('admin');

        $this->ids = [
            'category_id' => Category::create(['name' => 'Antibiotic'])->id,
            'unit_id' => Unit::create(['name' => 'Box'])->id,
            'manufacturer_id' => Manufacturer::create(['name' => 'Acme'])->id,
            'status_id' => Status::create(['name' => 'Available'])->id,
        ];
    }

    private function actAs(?string $role): User
    {
        $user = User::factory()->create();
        if ($role) {
            $user->assignRole(Role::findByName($role, 'web'));
        }
        Passport::actingAs($user, [], 'api');

        return $user;
    }

    private function addProduct(array $extra = []): int
    {
        $item = $this->postJson('/api/items', [
            'name' => 'Amoxicillin',
            'generic_name' => 'Amoxicillin',
            'strength' => '500mg',
            'dosage_form' => 'capsule',
            'reorder_level' => 20,
            'category_id' => $this->ids['category_id'],
            'unit_id' => $this->ids['unit_id'],
            ...$extra,
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/prices', ['item_id' => $item, 'amount' => 5.5])->assertSuccessful();

        return $item;
    }

    private function addBatch(int $item, string $batch, string $expires, int $qty, ?int $statusId = null)
    {
        return $this->postJson('/api/inventories', [
            'item_id' => $item,
            'status_id' => $statusId ?? $this->ids['status_id'],
            'manufacturer_id' => $this->ids['manufacturer_id'],
            'batch_number' => $batch,
            'p_date' => now()->toDateString(),
            'ex_date' => $expires,
            'stock_quantity' => $qty,
            'cost_price' => 4,
            'supplier' => 'Acme Pharma',
            'purchase_order' => 'PO-1',
        ]);
    }

    private function batch(int $item, string $batch, string $expires, int $qty, ?int $statusId = null): int
    {
        return $this->addBatch($item, $batch, $expires, $qty, $statusId)->assertCreated()->json('data.id');
    }

    public function test_sale_subtracts_stock_earliest_expiry_first_and_makes_an_invoice(): void
    {
        $item = $this->addProduct();
        $late = $this->batch($item, 'B-LATE', now()->addYear()->toDateString(), 50);
        $early = $this->batch($item, 'B-EARLY', now()->addMonths(2)->toDateString(), 10);

        $response = $this->postJson('/api/transactions', ['item_id' => $item, 'quantity' => 15])
            ->assertCreated()
            ->assertJsonCount(2, 'data.transactions');
        $this->assertEquals(82.5, $response->json('data.total'));

        $this->assertSame('INV-'.str_pad($response->json('data.id'), 6, '0', STR_PAD_LEFT), $response->json('data.invoice_number'));
        $this->assertSame(0, Inventory::find($early)->stock_quantity);
        $this->assertSame(45, Inventory::find($late)->stock_quantity);
    }

    public function test_cannot_sell_more_than_stock_or_expired_or_unsellable_stock(): void
    {
        $item = $this->addProduct();
        $batch = $this->batch($item, 'B1', now()->addYear()->toDateString(), 5);
        $quarantine = Status::create(['name' => 'Quarantine', 'is_sellable' => false])->id;
        $this->batch($item, 'B2', now()->addYear()->toDateString(), 100, $quarantine);
        Inventory::create([
            'item_id' => $item, 'status_id' => $this->ids['status_id'], 'manufacturer_id' => $this->ids['manufacturer_id'],
            'batch_number' => 'OLD', 'p_date' => '2020-01-01', 'ex_date' => '2021-01-01', 'stock_quantity' => 100, 'cost_price' => 1,
        ]);

        $this->postJson('/api/transactions', ['item_id' => $item, 'quantity' => 6])->assertStatus(422);
        $this->assertSame(5, Inventory::find($batch)->stock_quantity);
    }

    public function test_voiding_a_sale_restores_stock_and_invoice_total(): void
    {
        $item = $this->addProduct();
        $batch = $this->batch($item, 'B1', now()->addYear()->toDateString(), 10);

        $invoice = $this->postJson('/api/transactions', ['item_id' => $item, 'quantity' => 4])->json('data');
        $line = $invoice['transactions'][0]['id'];
        $this->assertSame(6, Inventory::find($batch)->stock_quantity);

        $this->deleteJson("/api/transactions/$line")->assertNoContent();

        $this->assertSame(10, Inventory::find($batch)->stock_quantity);
        $this->assertEquals(0, \App\Models\Invoice::find($invoice['id'])->total);
        $this->deleteJson("/api/transactions/$line")->assertNotFound();
    }

    public function test_stock_quantity_cannot_be_edited_directly(): void
    {
        $item = $this->addProduct();
        $batch = $this->batch($item, 'B1', now()->addYear()->toDateString(), 10);

        $this->putJson("/api/inventories/$batch", [
            'item_id' => $item, 'status_id' => $this->ids['status_id'], 'manufacturer_id' => $this->ids['manufacturer_id'],
            'batch_number' => 'B1', 'p_date' => now()->toDateString(), 'ex_date' => now()->addYear()->toDateString(),
            'cost_price' => 4, 'stock_quantity' => 999,
        ])->assertOk();

        $this->assertSame(10, Inventory::find($batch)->stock_quantity);
    }

    public function test_adjustments_and_history(): void
    {
        $item = $this->addProduct();
        $batch = $this->batch($item, 'B1', now()->addYear()->toDateString(), 10);

        $this->postJson('/api/stock-adjustments', ['inventory_id' => $batch, 'type' => 'damaged', 'quantity' => 3, 'reason' => 'Dropped'])->assertCreated();
        $this->postJson('/api/stock-adjustments', ['inventory_id' => $batch, 'type' => 'return', 'quantity' => 1, 'reason' => 'Customer return'])->assertCreated();
        $this->postJson('/api/stock-adjustments', ['inventory_id' => $batch, 'type' => 'lost', 'quantity' => 50, 'reason' => 'Missing'])->assertStatus(422);
        $this->postJson('/api/stock-adjustments', ['inventory_id' => $batch, 'type' => 'lost', 'quantity' => 1])->assertStatus(422);

        $this->assertSame(8, Inventory::find($batch)->stock_quantity);
        $this->getJson("/api/stock-movements?inventory_id=$batch")->assertOk()->assertJsonCount(3, 'data');
        $this->getJson("/api/stock-movements?inventory_id=$batch&type=damaged")->assertJsonCount(1, 'data');
    }

    public function test_batch_rules(): void
    {
        $item = $this->addProduct();
        $this->batch($item, 'B1', now()->addDays(20)->toDateString(), 5);

        $this->addBatch($item, 'B1', now()->addYear()->toDateString(), 5)->assertStatus(422);
        $this->addBatch($item, 'B2', now()->subDay()->toDateString(), 5)->assertStatus(422);
    }

    public function test_prescription_items_need_patient_details(): void
    {
        $item = $this->addProduct(['is_prescription_only' => true]);
        $this->batch($item, 'B1', now()->addYear()->toDateString(), 5);

        $this->postJson('/api/transactions', ['item_id' => $item, 'quantity' => 1])->assertStatus(422);
        $this->postJson('/api/transactions', [
            'item_id' => $item, 'quantity' => 1, 'patient_name' => 'Abebe', 'prescription_number' => 'RX-9',
        ])->assertCreated()->assertJsonPath('data.prescription_number', 'RX-9');
    }

    public function test_reports(): void
    {
        $item = $this->addProduct();
        $this->batch($item, 'B1', now()->addYear()->toDateString(), 5);

        $this->getJson('/api/reports/low-stock')->assertOk()->assertJsonPath('data.0.id', $item);
        $this->getJson('/api/reports/expiring?days=400')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_roles_are_enforced(): void
    {
        $item = $this->addProduct();
        $batch = $this->batch($item, 'B1', now()->addYear()->toDateString(), 5);

        $this->actAs('pharmacist');
        $this->getJson('/api/items')->assertOk();
        $this->postJson('/api/transactions', ['item_id' => $item, 'quantity' => 1])->assertCreated();
        $this->postJson('/api/items', [])->assertForbidden();
        $this->postJson('/api/prices', ['item_id' => $item, 'amount' => 1])->assertForbidden();
        $this->putJson("/api/inventories/$batch", [])->assertForbidden();
        $this->postJson('/api/stock-adjustments', [])->assertForbidden();
        $this->getJson('/api/user')->assertForbidden();

        $this->actAs(null);
        $this->getJson('/api/items')->assertForbidden();
        $this->postJson('/api/transactions', ['item_id' => $item, 'quantity' => 1])->assertForbidden();
    }

    public function test_role_command_gives_a_user_a_role(): void
    {
        $user = User::factory()->create(['email' => 'new@example.com']);

        $this->artisan('pharmacy:role new@example.com pharmacist')->assertSuccessful();
        $this->assertTrue($user->fresh()->hasRole('pharmacist'));
        $this->artisan('pharmacy:role nobody@example.com')->assertFailed();
    }
}
