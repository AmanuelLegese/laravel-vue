<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with(['category:id,name', 'unit:id,name', 'activePrice'])
            ->when(request('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$term}%")
                ->orWhere('generic_name', 'like', "%{$term}%")))
            ->latest()
            ->paginate($this->perPage());

        return ItemResource::collection($items);
    }

    public function store(ItemRequest $request): ItemResource
    {
            $item = Item::create($request->validated());
            return new ItemResource($item);
    }

    public function show(Item $item): ItemResource
    {
        return ItemResource::make($item);
    }

    public function update(ItemRequest $request, Item $item): ItemResource
    {
            $item->update($request->validated());
            return new ItemResource($item);
    }

    public function destroy(Item $item): Response
    {
            $item->delete();
            return response()->noContent();
    }
}
