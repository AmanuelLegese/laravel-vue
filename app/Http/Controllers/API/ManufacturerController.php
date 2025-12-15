<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManufacturerRequest;
use App\Http\Resources\ManufacturerResource;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManufacturerController extends Controller
{
    public function index()
    {
        return ManufacturerResource::collection(Manufacturer::latest()->paginate(10));
    }

    public function store(ManufacturerRequest $request): ManufacturerResource
    {
            $manufacturer = Manufacturer::create($request->validated());
            return new ManufacturerResource($manufacturer);
    }

    public function show(Manufacturer $manufacturer): ManufacturerResource
    {
        return ManufacturerResource::make($manufacturer);
    }

    public function update(ManufacturerRequest $request, Manufacturer $manufacturer): ManufacturerResource
    {
            $manufacturer->update($request->validated());
            return new ManufacturerResource($manufacturer);
    }

    public function destroy(Manufacturer $manufacturer): Response
    {
            $manufacturer->delete();
            return response()->noContent();
    }
}
