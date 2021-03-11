<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use phpDocumentor\Reflection\Types\Boolean;
use Illuminate\Support\Str;

class WechatContact extends Model
{
    use HasFactory;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

	use SoftDeletes;

    const TYPES = [
        'public'=>0, // 0
        'friend'=>1, // 1
        'group'=>2, // 2
        'stranger'=>3, // 3
    ];

    const SEX = [
        '未知',
        '男',
        '女'
    ];
    
    // // bot和contact关系 N:N
    // // @see https://laravel.com/docs/8.x/eloquent-relationships#many-to-many
    // public function bots(): BelongsToMany
    // {
    //     // $bot = $contact->bots->where('userName', 'wxid_7nof1pauaqyd22')->first();
    //     // $bot->pivot->remark
    //     // $bot->pivot->seat_user_id
    //     // $bot->pivot->config
    //     return $this->belongsToMany(WechatBot::class, 'wechat_bot_contacts')
    //         ->withPivot(['type','remark','seat_user_id']); //, 'wechat_contact_id', 'wechat_bot_id'
    // }

    /**
     * @return bool
     */
    public function isRoom()
    {
        return Str::endsWith($this->conversation,'@chatroom');
    }

    public function conversations()
    {
        // 为什么 Conversion 不显示所有的？
            // 因为太多，页面太长、太卡！

        //Conversion显示最近xx天的聊天记录： updated_at > now() — 30/90 
            // 🏅️：群30天，个人60天  //默认
            // 🏅️：群90天，个人180天
            // 🏅️：群180天，个人360天
            
        // TODO  Custom level 
        $history = now()->subDays($this->isRoom()?30:60);
        return $this->hasMany(WechatMessage::class, 'conversation')->where('updated_at', '>', $history);
    }
}

