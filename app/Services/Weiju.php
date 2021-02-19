<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class Weiju {
    private Array $data;
    public String $baseUri;

    public function __construct()
    {
        $phone = config('services.weiju.phone');
        $password = config('services.weiju.password');

        $this->baseUri = config('services.weiju.baseUri');
        $this->data = compact('phone', 'password');
    }
    
    public function getToken():Response { return Http::withOptions(['base_uri' => $this->baseUri])->post("/user/login", $this->data); }


    // 首次会掉，第二次登录传WXID就基本不掉，还要看权重
    // 第一次成功登录，获取 wxid_7xxx 作为第二次取码参数，会手机微信弹窗进行登录。防止掉线。
    // getQR => getwId => "http://weixin.qq.com/x/${wId}&size=250"
    public function getQR($Wxid = ''):Response {
        $endPoint = "/foreign/message/scanNew";
        $headers = [
            'token' => Cache::get('weiju_token',''),
            "content-type" => "application/json"
        ];

        if($Wxid){ //弹窗登录
            return Http::withOptions(['base_uri' => $this->baseUri])->withHeaders($headers)->post($endPoint, compact('Wxid'));
        }else{
            return Http::withOptions(['base_uri' => $this->baseUri])->withHeaders($headers)->post($endPoint);
        }
    }

}