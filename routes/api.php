<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);

Route::get('dashboard', function () {
    $activity = ActivityLog::query()->latest('id')->limit(6)->get();

    // Resolver los nombres en bloque evita una consulta por cada movimiento.
    $productIds = $activity->where('entity_type', 'Product')->pluck('entity_id')->unique();
    $categoryIds = $activity->where('entity_type', 'Category')->pluck('entity_id')->unique();

    $productNames = Product::query()->whereIn('id', $productIds)->pluck('name', 'id');
    $categoryNames = Category::query()->whereIn('id', $categoryIds)->pluck('name', 'id');

    // Si un registro fue eliminado, otro evento reciente puede conservar su nombre.
    $namesFromRecentEvents = $activity
        ->groupBy(fn (ActivityLog $entry) => "{$entry->entity_type}:{$entry->entity_id}")
        ->map(fn ($events) => $events
            ->map(fn (ActivityLog $event) => data_get($event->new_values, 'name')
                ?: data_get($event->old_values, 'name'))
            ->first(fn ($name) => filled($name)));

    $activity->each(function (ActivityLog $entry) use ($productNames, $categoryNames, $namesFromRecentEvents) {
        // Los eventos de creación y eliminación ya contienen el nombre histórico.
        // En una edición de stock o precio, recuperarlo del registro actual.
        $nameFromEvent = $entry->action === 'deleted'
            ? data_get($entry->old_values, 'name')
            : data_get($entry->new_values, 'name');

        $nameFromDatabase = match ($entry->entity_type) {
            'Product' => $productNames->get($entry->entity_id),
            'Category' => $categoryNames->get($entry->entity_id),
            default => null,
        };

        $entry->setAttribute('entity_name', $nameFromEvent
            ?: $nameFromDatabase
            ?: $namesFromRecentEvents->get("{$entry->entity_type}:{$entry->entity_id}"));
    });

    return response()->json([
        'success' => true,
        'message' => 'Resumen del inventario',
        'data' => [
            'products' => Product::count(),
            'categories' => Category::count(),
            'stock' => Product::sum('stock'),
            'inventory_value' => (float) Product::query()
                ->selectRaw('COALESCE(SUM(price * stock), 0) AS total')
                ->first()?->total,
            'recent_activity' => $activity,
        ],
    ]);
});

Route::get('docs', fn () => response()->view('api-docs'));
Route::get('openapi.yaml', fn () => response()->file(
    public_path('docs/openapi.yaml'),
    ['Content-Type' => 'application/yaml']
));
