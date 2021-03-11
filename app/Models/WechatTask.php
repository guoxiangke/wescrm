<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WechatTask extends Model
{
    use HasFactory;
    protected $guarded = ['id', 'created_at', 'updated_at'];
    // $content = json_decode(trim($this->content), true);
    
    protected $casts = [
        'config' => 'array',
    ];


    public function getTypeAttribute($value)
    {
        if(is_null($value)) return "series"; // 系列发送 from 群发库
        return WechatContent::TYPES_CN[$value];
    }


    // public function subscriptions(){
    //     return $this->belongsToMany(WechatTask::class);
    // }
}
