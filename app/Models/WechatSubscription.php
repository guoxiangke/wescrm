<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatSubscription extends Model
{
    use HasFactory;
	use SoftDeletes;
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    protected $casts = [
        'content' => 'array',
    ];

    // 1:1
    public function task(){
        return $this->hasOne(WechatTask::class);
    }
}
