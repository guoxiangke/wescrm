<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Metable\Metable;
// use Illuminate\Database\Eloquent\SoftDeletes;

class WechatBotContact extends Model
{
    use Metable; // config
    use \Spatie\Tags\HasTags;
    // use SoftDeletes;
    use HasFactory;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    
    const TYPES = WechatContact::TYPES;



    // 1:1
    public function contact(){
        return $this->hasOne(WechatContact::class, 'id', 'wechat_contact_id');
    }

    public function seat(){
        return $this->belongsTo(User::class, 'seat_user_id');
    }

    public function bot(){
        return $this->belongsTo(WechatBot::class, 'wechat_bot_id');
    }
}
