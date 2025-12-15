<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(Category::latest()->paginate(10));
    }

    public function store(CategoryRequest $request): CategoryResource
    {
            $category = Category::create($request->validated());
            return new CategoryResource($category);
    }

    public function show(Category $category): CategoryResource
    {
        return CategoryResource::make($category);
    }

    public function update(CategoryRequest $request, Category $category): CategoryResource
    {
            $category->update($request->validated());
            return new CategoryResource($category);
    }

    public function destroy(Category $category): Response
    {
            $category->delete();
            return response()->noContent();
        
    }
}
