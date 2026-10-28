<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InventoryApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_listados_y_dashboard_responden_json(): void
    {
        $this->getJson('/api/categories')->assertOk()->assertJsonPath('success', true);
        $this->getJson('/api/products')->assertOk()->assertJsonPath('success', true);
        $this->getJson('/api/dashboard')->assertOk()->assertJsonStructure(['success', 'message', 'data' => ['products', 'categories', 'stock', 'inventory_value', 'recent_activity']]);
    }

    public function test_puede_crear_editar_y_eliminar_categoria(): void
    {
        $created = $this->postJson('/api/categories', ['name' => 'Prueba categoría ' . uniqid()]);
        $created->assertCreated()->assertJsonPath('success', true);
        $id = $created->json('data.id');

        $this->putJson("/api/categories/$id", ['name' => 'Categoría modificada ' . uniqid()])->assertOk();
        $this->deleteJson("/api/categories/$id")->assertOk()->assertJsonPath('success', true);
    }

    public function test_puede_crear_editar_y_eliminar_producto(): void
    {
        $category = Category::create(['name' => 'Categoría prueba ' . uniqid()]);
        $data = ['name' => 'Producto prueba', 'description' => null, 'price' => 25.50, 'stock' => 10, 'category_id' => $category->id];
        $created = $this->postJson('/api/products', $data);
        $created->assertCreated()->assertJsonPath('success', true);
        $id = $created->json('data.id');

        $data['stock'] = 4;
        $this->putJson("/api/products/$id", $data)->assertOk()->assertJsonPath('data.stock', 4);
        $this->deleteJson("/api/products/$id")->assertOk()->assertJsonPath('success', true);
    }

    public function test_precio_negativo_devuelve_422(): void
    {
        $category = Category::create(['name' => 'Categoría prueba ' . uniqid()]);
        $this->postJson('/api/products', ['name' => 'Inválido', 'price' => -1, 'stock' => 1, 'category_id' => $category->id])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('price');
    }

    public function test_no_se_elimina_categoria_con_productos(): void
    {
        $category = Category::create(['name' => 'Categoría prueba ' . uniqid()]);
        Product::create(['name' => 'Producto prueba', 'price' => 5, 'stock' => 1, 'category_id' => $category->id]);
        $this->deleteJson("/api/categories/{$category->id}")
            ->assertStatus(409)->assertJsonPath('success', false);
    }

    public function test_registra_creacion_en_auditoria(): void
    {
        $category = Category::create(['name' => 'Categoría auditada ' . uniqid()]);
        $this->assertTrue(ActivityLog::where('entity_type', 'Category')->where('entity_id', $category->id)->where('action', 'created')->exists());
    }

    public function test_producto_inexistente_devuelve_404(): void
    {
        $this->getJson('/api/products/999999999')->assertNotFound()->assertJsonPath('success', false);
    }
}
