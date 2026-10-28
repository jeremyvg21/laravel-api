<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityLog extends Model {
    protected $fillable = ['entity_type','entity_id','action','actor_id','old_values','new_values'];
    protected $casts = ['old_values'=>'array','new_values'=>'array'];
}
