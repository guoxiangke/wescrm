<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class WechatAutoReply extends Model
{
    use HasFactory;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at']; // If you choose to unguard your model, you should take special care to always hand-craft the arrays passed to Eloquent's fill, create, and update methods: https://laravel.com/docs/8.x/eloquent#mass-assignment-json-columns

	use SoftDeletes;
    use LogsActivity;
    protected static $logOnlyDirty = true;
    protected static $logAttributesToIgnore = ['bigHead', 'config'];
    
    // WechatBot::find(1)->autoReplies()->create(['keyword'=>'hi','wechat_content_id'=>1]);
    // keyword： @see Str::is()
        // ping 表示完全匹配关键词，区分大小写
        // ping* 表示以ping开头
        // *ping 表示以ping结尾
        // *ping* 表示含有ping的内容
        // *ping*pong* 表示含有ping之后，又有pong 
        // 例如：*你好***阿门*


    public function content()
    {
        return $this->hasOne(WechatContent::class, 'id', 'wechat_content_id');
    }
}
