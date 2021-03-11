<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatContent extends Model
{
    use HasFactory;
	use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    // 可以主动发送的消息类型 @see App\Services\WechatBot->sendApp()
    const TYPES_CN = ['文本模版','文本','图片','视频','文件','名片','链接','小程序'];
    const TYPES = [
        "template",   // 0 含模版变量的文本内容
        "text",//文本 1 // 话术库
        "image",//图片 2
        "video",//视频 3
        "file",//mp3文件等 4
        "card",//名片 5
        "url",//链接 6
        "app",//小程序 7
    ];
    const TYPE_TEMPLATE = 0;
    const TYPE_TEXT = 1;
    
    protected $casts = [
        'content' => 'array',
    ];

    public function getCnTypeAttribute()
    {
        return self::TYPES_CN[$this->type];
    }

    public function getContentASText()
    {
        $content = '';
        $typeName = self::TYPES[$this->type];
        switch ($typeName) {
            case 'template':
            case 'text':
            case 'image':
                $content =  $this->content['data']['content'];
                break;
            case 'video':
                $content =  $this->content['data']['path'];
                break;

            case 'file':
                // $content =  $this->content['data']['content'];
                break;

            case 'card':
                $content =  $this->content['data']['nameCardId'];
                break;

            case 'url':
                $content =  $this->content['data']['url'];
                break;

            case 'app':
                // $content =  $this->content['data']['content'];
                break;
            default:
                # code...
                break;
        }
        return $content;
    }
}
