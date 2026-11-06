<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AdminActivityLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['actor_id','actor_name','action','module','method','path','ip_address','user_agent','status_code','created_at'];
    protected function casts(): array { return ['created_at'=>'datetime']; }
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
}
