<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
class CategoryController extends Controller {
    public function index(): JsonResponse {
        return response()->json(['success'=>true,'message'=>'Categorías obtenidas',
            'data'=>Category::withCount('products')->orderBy('name')->get()]);
    }
    public function store(CategoryRequest $request): JsonResponse {
        $category = Category::create($request->validated());
        return response()->json(['success'=>true,'message'=>'Categoría creada','data'=>$category],201);
    }
    public function show(Category $category): JsonResponse {
        return response()->json(['success'=>true,'message'=>'Categoría encontrada','data'=>$category->loadCount('products')]);
    }
    public function update(CategoryRequest $request, Category $category): JsonResponse {
        $category->update($request->validated());
        return response()->json(['success'=>true,'message'=>'Categoría actualizada','data'=>$category->fresh()->loadCount('products')]);
    }
    public function destroy(Category $category): JsonResponse {
        if ($category->products()->exists()) {
            return response()->json(['success'=>false,'message'=>'No se puede eliminar una categoría que tiene productos',
                'errors'=>['category'=>['Primero elimina o reasigna sus productos.']]],409);
        }
        $category->delete();
        return response()->json(['success'=>true,'message'=>'Categoría eliminada','data'=>null]);
    }
}
