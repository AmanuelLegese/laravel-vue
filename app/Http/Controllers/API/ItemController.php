<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    /**
     * Display a listing of the items.
     *
     * @return JsonResource
     */
    public function index(): JsonResource
    {
        // Paginate the items
        return ItemResource::collection(Item::latest()->paginate(10));
    }

    /**
     * Store a newly created item in storage.
     *
     * @param ItemRequest $request
     * @return ItemResource
     */
    public function store(ItemRequest $request): ItemResource
    {
        // Validate request automatically via ItemRequest
        $validatedData = $request->validated();

        // Create the item and return as ItemResource
        $item = Item::create($validatedData);
        return new ItemResource($item);
    }

    /**
     * Display the specified item.
     *
     * @param Item $item
     * @return ItemResource
     */
    public function show(Item $item): ItemResource
    {
        return ItemResource::make($item);
    }

    /**
     * Update the specified item in storage.
     *
     * @param ItemRequest $request
     * @param Item $item
     * @return ItemResource
     */
    public function update(ItemRequest $request, Item $item): ItemResource
    {
        // Validate request automatically via ItemRequest
        $validatedData = $request->validated();

        // Update the item and return as ItemResource
        $item->update($validatedData);
        return new ItemResource($item);
    }

    /**
     * Remove the specified item from storage.
     *
     * @param Item $item
     * @return JsonResponse
     */
    public function destroy(Item $item): Response
    {
        $item->delete();
        return response()->noContent();
    }
}
