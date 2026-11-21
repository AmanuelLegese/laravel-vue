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
use Tests\TestCase;

class PharmacyInventoryTest extends TestCase
{
    use RefreshDatabase;

    private array $ids;

    protected function setUp(): void
    {
        parent::setUp();
        Passport::actingAs(User::factory()->create(), [], 'api');

        $this->ids = [
            'category_id' => Category::create(['name' => 'Antibiotic'])->id,
            'unit_id' => Unit::create(['name' => 'Box'])->id,
            'manufacturer_id' => Manufacturer::create(['name' => 'Acme'])->id,
            'status_id' => Status::create(['name' => 'Available'])->id,
        ];
    }

    private function addProduct(): int
    {
        $item = $this->postJson('/api/items', [
            'name' => 'Amoxicillin',
            'generic_name' => 'Amoxicillin',
            'strength' => '500mg',
            'dosage_form' => 'capsule',
            'reorder_level' => 20,
            'category_id' => $this->ids['category_id'],
            'unit_id' => $this->ids['unit_id'],
        ])->assertCreated()->json('data.id');

        $this->postJson('/api/prices', ['item_id' => $item, 'amount' => 5.5])->assertSuccessful();

        return $item;
    }

    private function addBatch(int $item, string $batch, string $expires, int $qty): int
    {
        return $this->postJson('/api/inventories', [
            'item_id' => $item,
            'status_id' => $this->ids['status_id'],
            'manufacturer_id' => $this->ids['manufacturer_id'],
            'batch_number' => $batch,
            'p_date' => now()->toDateString(),
            'ex_date' => $expires,
            'stock_quantity' => $qty,
            'single_price' => 4,
        ])->assertCreated()->json('data.id');
    }

    public function test_sale_subtracts_stock_earliest_expiry_first(): void
    {
        $item = $this->addProduct();
        $late = $this->addBatch($item, 'B-LATE', now()->addYear()->toDateString(), 50);
        $early = $this->addBatch($item, 'B-EARLY', now()->addMonths(8)->toDateString(), 10);

        $this->postJson('/api/transactions', ['item_id' => $item, 'quantity' => 15])->assertSuccessful();

        $this->assertSame(0, Inventory::find($early)->stock_quantity);
        $this->assertSame(45, Inventory::find($late)->stock_quantity);
    }

    public function test_cannot_sell_more_than_stock(): void
    {
        $item = $this->addProduct();
        $batch = $this->addBatch($item, 'B1', now()->addYear()->toDateString(), 5);

        $this->postJson('/api/transactions', ['item_id' => $item, 'quantity' => 6])->assertStatus(422);
        $this->assertSame(5, Inventory::find($batch)->stock_quantity);
    }

    public function test_reports(): void
    {
        $item = $this->addProduct();
        $this->addBatch($item, 'B1', now()->addYear()->toDateString(), 5);

        $this->getJson('/api/reports/low-stock')->assertOk()->assertJsonPath('data.0.id', $item);
        $this->getJson('/api/reports/expiring?days=400')->assertOk()->assertJsonCount(1, 'data');
    }
}
