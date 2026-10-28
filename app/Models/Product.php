<?php
namespace App\Models;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Product extends Model {
    use RecordsActivity;
    protected $fillable = ['name','description','price','stock','category_id'];
    protected $casts = ['price'=>'decimal:2','stock'=>'integer','category_id'=>'integer'];
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
}
