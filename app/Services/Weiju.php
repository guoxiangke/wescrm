<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Weiju {
    private $http;
    public String $baseUri; //depended by Weixin.php

    public function __construct() {
        $this->baseUri = config('services.weiju.baseUri');
        $this->http = Http::withOptions(['base_uri' => $this->baseUri]);
    }
    
    // "code":1,
    // "msg":"登录成功",
    // "data":
        // "name":,
        // "apikey":,
        // "num":2,
        // "expiretime":"2021-04-02 11:21:16"
    public function getStatus(){
        return rescue(fn() => $this->http->post("/user/login", config('services.weiju.account')), null, false);
    }


    // 首次会掉，第二次登录传WXID就基本不掉，还要看权重
    // 第一次成功登录，获取 wxid_7xxx 作为第二次取码参数，会手机微信弹窗进行登录。防止掉线。
    // getQR => getwId => "http://weixin.qq.com/x/${wId}&size=250"
    public function login($Wxid = ''){
        $endPoint = "/foreign/message/scanNew";
        $headers = [
            'token' => option('weiju.token'), // @see line:156 App\Http\Livewire\Weixin::mount();
            "content-type" => "application/json"
        ];
        // 弹窗登录 || 扫码登录
        $data = $Wxid?compact('Wxid'):[];
        return rescue(fn()=>$this->http->withHeaders($headers)->post($endPoint, $data), null, false);
    }

}