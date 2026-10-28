<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardActivityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_expone_nombre_en_creacion_actualizacion_y_eliminacion(): void
    {
        $category = Category::create(['name' => 'Categoría actividad ' . uniqid()]);
        $product = Product::create([
            'name' => 'Producto actividad ' . uniqid(),
            'price' => 15.50,
            'stock' => 3,
            'category_id' => $category->id,
        ]);
        $name = $product->name;
        $product->update(['stock' => 4]);
        $product->delete();

        $response = $this->getJson('/api/dashboard');
        $response->assertOk()->assertJsonPath('success', true);

        $logs = collect($response->json('data.recent_activity'))
            ->where('entity_type', 'Product')
            ->where('entity_id', $product->id);

        $this->assertSame(3, $logs->count());
        foreach ($logs as $log) {
            $this->assertSame($name, $log['entity_name']);
        }
    }
}
