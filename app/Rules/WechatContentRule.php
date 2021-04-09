<?php

namespace App\Rules;

use App\Models\WechatContent;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class WechatContentRule implements Rule
{
    private $type;
    private $message = 'Content 格式错误：';
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $data = json_decode($value, 1);
        $type = WechatContent::TYPES[$this->type];
        //TODO validate!
        switch ($type) {
            case 'template':
            case 'text':
                if(!isset($data['content'])){
                    $this->message .= '内容必需包含  {"content": "';
                    return false;
                }
                break;
            case 'image':
                if(!Str::endsWith($data['content'], ['.jpg','.png','.jpeg','.gif'])) {
                    $this->message .= '图片格式不对，必需以 jpg,png,jpeg,gif 结尾！';
                    return false;
                }
                if(!Str::startsWith($data['content'], 'http')) {
                    $this->message .= '图片链接不对';
                    return false;
                }
                break;
            case 'video':
                if(!Arr::has($data, ['path', 'thumbPath'])) {
                    $this->message .= '缺少必要字段';
                    return false;
                }
                if(!Str::startsWith($data['path'], 'http') || !Str::endsWith($data['path'], '.mp4')) {
                    $this->message .= '视频地址不对';
                    return false;
                }
                if(!Str::startsWith($data['thumbPath'], 'http')) {
                    $this->message .= '缩略图链接不对';
                    return false;
                }
                break;
            case 'url':
                if(!Arr::has($data, ['title', 'url', 'description', 'thumbUrl'])) {
                    $this->message .= '缺少必要字段';
                    return false;
                }
                if(!Str::startsWith($data['url'], 'http')) {
                    $this->message .= '链接不对';
                    return false;
                }
                if(!Str::startsWith($data['thumbUrl'], 'http')) {
                    $this->message .= '缩略图链接不对';
                    return false;
                }
                break;
            case 'card':
                if(!Arr::has($data, ['nameCardId', 'nickName'])) {
                    $this->message .= '缺少必要字段';
                    return false;
                }
                break;
            default:
                # code...
                break;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
