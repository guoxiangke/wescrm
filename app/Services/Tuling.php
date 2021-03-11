<?php
namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Tuling {

    public String $baseUri;

    public function __construct()
    {
        $this->baseUri = "http://openapi.tuling123.com";
    }
    
    public function post($text='你好'):Response {
        $data = [
            'reqType'=> 0,
            'perception'=>[
                'inputText'=>['text' => $text],
            ],
            'userInfo' => [
                "apiKey"=> option('tuling.token','88c9a1a8af8b4e6cb071a5033d81bc6c'),
                "userId"=> option('tuling.token','733979'),
            ]
        ];
        return Http::withOptions(['base_uri' => $this->baseUri])
            ->post("/openapi/api/v2", $data);
        // // 优先回复文本
        // if($response->ok())
        // foreach ($response['results'] as $result) {
        //     if($result['resultType'] == 'text'){
        //         return $result['value']['text'];
        //     }
        // }
    }
}