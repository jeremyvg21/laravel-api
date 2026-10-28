<?php
namespace App\Models;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Category extends Model {
    use RecordsActivity;
    protected $fillable = ['name','description'];
    public function products(): HasMany { return $this->hasMany(Product::class); }
}
