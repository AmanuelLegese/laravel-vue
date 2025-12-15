<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    public function index()
    {
        $item = Cache::remember('Item',3200,function(){
            return Item::latest()->paginate(10);
        });
        return ItemResource::collection($item);
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
