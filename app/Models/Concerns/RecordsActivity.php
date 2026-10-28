<?php
namespace App\Models\Concerns;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
trait RecordsActivity {
    protected array $auditOriginal = [];
    protected static function bootRecordsActivity(): void {
        static::updating(function (Model $model): void {
            $model->auditOriginal = $model->getOriginal();
        });
        static::created(function (Model $model): void {
            self::recordActivity($model, 'created', null, $model->getAttributes());
        });
        static::updated(function (Model $model): void {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            if (count($changes) === 0) return;
            self::recordActivity($model, 'updated',
                Arr::only($model->auditOriginal, array_keys($changes)),
                Arr::only($model->getAttributes(), array_keys($changes)));
        });
        static::deleted(function (Model $model): void {
            self::recordActivity($model, 'deleted', $model->getAttributes(), null);
        });
    }
    protected static function recordActivity(Model $model, string $action, ?array $before, ?array $after): void {
        ActivityLog::create([
            'entity_type' => class_basename($model),
            'entity_id' => $model->getKey(),
            'action' => $action,
            'actor_id' => auth()->id(),
            'old_values' => $before,
            'new_values' => $after,
        ]);
    }
}
