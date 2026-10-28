<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class ProductController extends Controller {
    public function index(Request $request): JsonResponse {
        $query = Product::with('category');
        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name','like',"%{$search}%")->orWhere('description','like',"%{$search}%");
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }
        return response()->json(['success'=>true,'message'=>'Productos obtenidos','data'=>$query->latest('id')->get()]);
    }
    public function store(ProductRequest $request): JsonResponse {
        $product = Product::create($request->validated());
        return response()->json(['success'=>true,'message'=>'Producto creado','data'=>$product->load('category')],201);
    }
    public function show(Product $product): JsonResponse {
        return response()->json(['success'=>true,'message'=>'Producto encontrado','data'=>$product->load('category')]);
    }
    public function update(ProductRequest $request, Product $product): JsonResponse {
        $product->update($request->validated());
        return response()->json(['success'=>true,'message'=>'Producto actualizado','data'=>$product->fresh()->load('category')]);
    }
    public function destroy(Product $product): JsonResponse {
        $product->delete();
        return response()->json(['success'=>true,'message'=>'Producto eliminado','data'=>null]);
    }
}
