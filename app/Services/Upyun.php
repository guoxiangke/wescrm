<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

use Upyun\Config;
use Upyun\Signature;
use Upyun\Util;
use Upyun\Upyun as Upservice;

class Upyun {
    // public String $baseUri = 'https://v0.api.upyun.com';
    public $bucket;
    public $upyun;

    public function __construct() {
        $this->bucket = config('services.upyun.service');
        $this->config = new Config($this->bucket, config('services.upyun.operator'), config('services.upyun.password'));
        // $this->config->setFormApiKey('Mv83tlocuzkmfKKUFbz2s04FzTw=');
        $this->config->processNotifyUrl = 'https://console.upyun.com';
        $this->upyun = new Upservice($this->config);

        // $this->http = Http::withOptions(['base_uri' => $this->baseUri]);
    }
    

    // bucket	是	文件上传到的服务
    // save-key	是	文件保存路径，可用占位符，见 路径设置
    // expiration	是	请求的过期时间，UNIX UTC 时间戳，单位秒。建议设为 30 分钟
    public function authorization($savePath)
    {
        $data['save-key'] = $savePath;
        $data['bucket'] = $this->bucket;
        $data['expiration'] = time() + 120;
        $policy = Util::base64Json($data);

        $method = 'POST';
        $uri = '/' . $this->bucket;
        $authorization = Signature::getBodySignature($this->config, $method, $uri, null, $policy);
        return compact('policy', 'authorization');
    }

    public function silk($path)
    {
        // 使用时，按文档和个人需求填写tasks
        $tasks = array([
            'type' => 'audio',
            'avopts' => '/ab/20/ac/1/f/mp3',
            'save_as' => "$path.mp3",
        ]);
        return $this->upyun->process($tasks, Upservice::$PROCESS_TYPE_MEDIA, $path);
    }

    public function status($tasks)
    {
        return $this->upyun->queryProcessStatus($tasks);
    }

    public function has($path)
    {
        return $this->upyun->has($path);
    }

    public function delete($path)
    {
        return $this->upyun->delete($path);
    }
}