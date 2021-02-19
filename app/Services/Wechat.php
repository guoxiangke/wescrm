<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;

// Wechat API
class Wechat {
    private $http;
    private Array $data; // Http::post(uri,data)
    private String $wId; // wId from getQR
    private String $Wxid;   

    public function __construct($Wxid="", $wId="")
    {
        $weiju = app(Weiju::class);
        $this->http = Http::withOptions(['base_uri' => $weiju->baseUri])
            ->withHeaders([
                'token' => Cache::get('weiju_token', ''),
                "content-type" => "application/json"
            ]);
        $this->Wxid = $Wxid;
        $this->wId = $wId;
        $this->data = compact('Wxid');
    }

    ## 执行微信登录
    ## 循环执行 直到 扫描确认成功，成功后需要后台自动执行init！($timeout = 300; @see App\Jobs\InitWechat)
    public function login():Response { return $this->http->post("/foreign/message/checkQrCode", ["Wxid" => $this->wId]);}
    // {"code":-8,"msg":"异常：[Key:w99iG4V-qfONUNp5dtfp]数据不存在","data":{}}
    # 登出
    public function logout():Response { return $this->http->post("/foreign/wacat/out", $this->data);}

    # 数据加载中...
    public function init():Response { return $this->http->post("/foreign/tools/init", $this->data);}
    # 获取bot个人信息
    public function who():Response { return $this->http->post("/foreign/message/wxInfo", $this->data);}
    # heartBeat 检测是否在线 心跳检测
    public function isOnline():Response { return $this->http->post("/foreign/message/heartBeat", $this->data);}

    
    // region begin 好友操作

    # 获取联系人列表(群group、好友friend、公众号public)
    public function getAllContacts():Response { return $this->http->post("/foreign/message/getAllContact", $this->data);}
    public function getFriendList():Response { return $this->http->post("/foreign/friends/getFriendList", $this->data);}
    
    // 查找用户 Endpoint: /searchUser 不可靠，使用 /chat，
    // 返回内容不含 labelId，非好友不包含v1
    public function friendSearch($ToWxid):Response { return $this->http->post("/foreign/friends/chat", array_merge($this->data, get_defined_vars()));}
    // 此接口不支持直接获取wxid开头的微信号信息！！！// wxid开头的微信号，调用⬆️搜索用户接口⬆️获取信息
    public function friendFind($ToWxid):Response {
        // if(Str::startsWith($ToWxid, 'wxid_')) return $this->http->post("/foreign/friends/chat", array_merge($this->data, get_defined_vars()));
        return $this->http->post("/foreign/friends/searchUser", array_merge($this->data, get_defined_vars()));
    }

    #修改好友备注
    public function friendRemark($ToWxid,$remark):Response { return $this->http->post("/foreign/friends/remark", array_merge($this->data, get_defined_vars()));}
    #删除好友
    public function friendDel($ToWxid):Response { return $this->http->post("/foreign/friends/friendsDel", array_merge($this->data, get_defined_vars()));}
    #同意添加好友
        // 参数中的v1 v2是从消息回调中取得的。（PS：收到好友请求时，消息回调会收到一条添加消息，v1 v2在里面。）
    public function friendAgree($v1, $v2, $type=14):Response { return $this->http->post("/foreign/friends/passAddFriends", array_merge($this->data, get_defined_vars()));}
    #主动添加好友
        // $type 传3即可，添加来源：1-QQ号搜索，3-微信号搜索，4-QQ好友，8-通过群聊，12-来自QQ好友，14-通过群聊，15-手机号
        // v1必填	string	从搜索用户接口获取 
        // v2必填	string	从搜索用户接口获取
    public function friendAdd($v1, $v2, $type=15, $verify="Hi"):Response { return $this->http->post("/foreign/friends/passAddFriends", array_merge($this->data, get_defined_vars()));}
    
    #设置个人头头像 path为Url链接
    public function selfSetAvatar($path):Response { return $this->http->post("/foreign/friends/sendHeadImage", array_merge($this->data, get_defined_vars()));}
    public function selfGetQr():Response { return $this->http->post("/foreign/friends/getQrCode", $this->data);}
    #get62Data 
    // {
    //     "code": 1000,
    //     "msg": "成功",
    //     "data": {
    //       "Code": 0,
    //       "Success": true,
    //       "Message": "成功",
    //       "Data": "[Key:]数据不存在",
    //       "Debug": ""
    //     }
    //   }
    public function get62Data():Response { return $this->http->post("/foreign/tools/get62Data", $this->data);}
    // region end 好友操作



    // region begin 消息接收

    public function setCallBackUrl($callbackSend=false, $heartBeat='', $linkMsgSend=''):Response {
        if(!$callbackSend) $callbackSend = config('services.weiju.callbackUri');
        $this->unsetCallBackUrl();
        $data = array_merge($this->data, get_defined_vars());
        return $this->http->post("/foreign/user/setUrl", $data);
    }
    
    private function unsetCallBackUrl():Response { return $this->http->post("/foreign/user/delUrl", $this->data); }
    
    // 获取websocket连接服务地址
    // {
    //     "message": "成功",
    //     "code": "1000",
    //     "data": {
    //       "socketServer": "1x2.31.61.155",
    //       "port": 42*29
    //     }
    //   }
    public function getSocketServer():Response { return $this->http->post("/foreign/tools/getSocketServer", $this->data);}
    
    // POST 获取在线微信数量
    public function getOnlineWeixin():Response { return $this->http->post("/foreign/tools/getOnlineWeixin", $this->data);}
    
    public function saveAttachmentResponse($msgType, $msgId, $ToWxid, $content):Response {
        switch ($msgType) {
            case '3':
                $msgType = 'Img';
                break;
            case '34':
                $msgType = 'Voice';
                break;
            case '43':
                $msgType = 'Video';
                break;
            default:
                $msgType = 'File';
                break;
        }
        $data = array_merge($this->data, compact('msgId', 'ToWxid', 'content'));
        // Log::error('saveAttachmentResponse', $data);
        // getMsgFile getMsgImg getMsgVideo getMsgVoice
        return $this->http->post("/foreign/tools/getMsg$msgType", $data);

        // 成功后再通过data url下载保存文件，md5
    }

    // region end 消息接收 


    // region begin 消息发送 
    // TODO 去队列里发送！?
    public function send($sendEndpoint, Array $data):Response 
    {
        sleep(1);
        $response = $this->http->post("/foreign/message/$sendEndpoint", $data);
        return $response;
    }

    //发送文本消息
    public function sendText($ToWxid, $content, $at=""):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        $sendEndpoint = 'sendText';
        return $this->send($sendEndpoint, $data);
    }

    //发送图片消息
    public function sendImage($ToWxid, $content):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        $sendEndpoint = 'sendImage';
        return $this->send($sendEndpoint, $data);
    }

    //发送链接消息
    public function sendUrl($ToWxid, Array $link):Response 
    {
        // $link = compact('title', 'url', 'description', 'thumbUrl');
        $data =  array_merge($this->data, compact('ToWxid'), $link);
        $sendEndpoint = 'sendUrl';
        return $this->send($sendEndpoint, $data);
    }

    //发送名片消息
    public function sendCard($ToWxid, $nameCardId, $nickName=''):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        $sendEndpoint = 'sendCard';
        return $this->send($sendEndpoint, $data);
    }


    public function sendVideo($ToWxid,  $path, $thumbPath):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        $sendEndpoint = 'sendVideo';
        return $this->send($sendEndpoint, $data);
    }

    // TODO 测试发送小程序
    public function sendApp($ToWxid, Array $app):Response 
    {
        // $app = compact('appName', 'title', 'description', 'thumbUrl', 'thumbAesKey', 'pagePath');
        $data = array_merge($this->data, get_defined_vars());
        $sendEndpoint = 'sendApp';
        return $this->send($sendEndpoint, $data);
    }

    // 转发图片、视频、文件
    public function forword($ToWxid, $xmlContent) :Response 
    {
        $data = array_merge($xmlContent, get_defined_vars());
        $msg = xStringToArray($xmlContent);
        if(Arr::has($msg, 'img')){
            $sendEndpoint = 'sendRecvImage'; // sendRecvVideo //sendRecvFile
        }
        
        return $this->send($sendEndpoint, $data);
    }

    // region end 消息发送
}