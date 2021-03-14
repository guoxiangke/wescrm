<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
                'token' => option('weiju.token'),//Cache::get('weiju_token', ''),
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
    
    // 查找用户 /searchUser 不可用，使用 /chat
    // public function friendSearch($ToWxid):Response { return $this->http->post("/foreign/friends/chat", array_merge($this->data, get_defined_vars()));}
    // 此接口仅支持直接获取以wxid开头的微信号信息！！！// wxid开头的微信号，调用⬆️搜索用户接口⬆️获取信息
    
    // 查找即陌生/好友 获取V1 V2 用于主动添加好友
    public function friendFind($ToWxid):Response {
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
    public function friendAdd($v1, $v2, $type=3, $verify="Hi"):Response { return $this->http->post("/foreign/friends/passAddFriends", array_merge($this->data, get_defined_vars()));}
    
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

    public function setCallBackUrl($callbackSend=null, $heartBeat='', $linkMsgSend=''):Response {
        if(!$callbackSend) $callbackSend = route('webhook.weiju');
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
    // $wechat->send("sendText", ['ToWxid'=>'filehelper', 'content'=>$this->msg]);
    public function send($sendEndpoint, Array $content):Response 
    {
        sleep(1);
        $data = array_merge($this->data, $content);
        $response = $this->http->post("/foreign/message/$sendEndpoint", $data);
        return $response;
    }
    
    // TODO 测试发送小程序
    public function sendApp($ToWxid, Array $app):Response 
    {
        // $app = compact('appName', 'title', 'description', 'thumbUrl', 'thumbAesKey', 'pagePath');
        $data = array_merge($this->data, get_defined_vars());
        $sendEndpoint = 'sendApp';
        return $this->send($sendEndpoint, $data);
    }

    // 消息转发 
    // 图片、视频、文件 提供的有转发接口（foreign/message/sendRecvImage），其他使用主动发送方式转发
    // $content => $xmlRawContent with ToWxid
    // xmlRawContent = ['ToWxid'=>'filehelper', 'content'=>'<xml...']);
    public function forword($type, $content):Response
    {
        if(in_array($type,['image','video','file'])){
            $sendEndpoint = 'sendRecv' . Str::camel("{$type}"); // sendRecvImage sendRecvVideo sendRecvFile
        }else{
            // text card link ...
            $sendEndpoint = Str::camel("send_{$type}");
        }
        return $this->send($sendEndpoint, $content);
    }

    // region end 消息发送

    // 获取标签列表
    
    // TODO 标签初始化！
    public function getLables():Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        return $this->send('/foreign/Wacat/getLables', $data);
    }


    // region 群操作
    #

    # 创建微信群
        // "topic": "测试创建微信",
        // "userNameList": "wxid_mij04zvb1m1w31,M952320157,wxid_om8j3vmdyivs22"
    public function groupAdd($topic, $userNameList):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        return $this->send('/foreign/group/groupAdd', $data);
    }
    # 获取群详情
    public function getGroupInfo($ToWxid):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        return $this->send('/foreign/group/getGroupInfo', $data);
    }
    # 设置群公告
    public function setGroupTopic($chatroom, $topic):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        return $this->send('/foreign/group/setName', $data);
    }
    # 设置群公告
    public function setGroupNotic($ToWxid, $content):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        return $this->send('/foreign/group/setGroupNotic', $data);
    }
    # 获取群二维码
    public function getGroupQrCode($ToWxid):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        return $this->send('/foreign/group/getGroupQrCode', $data);
    }
    # 群保存/取消到通讯录
    public function seveGroup($chatroom, $isShow=true):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        return $this->send('/foreign/group/seveGroup', $data);
    }
    #  添加群成员
    public function addMember($ToWxid, $chatroom):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        return $this->send('/foreign/group/addMember', $data);
    }
    # 邀请群成员
    public function invateMember($ToWxid, $chatroom):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        return $this->send('/foreign/group/invateMember', $data);
    }
    # 踢人
    public function delMember($ToWxid, $chatRoomToWxid):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        return $this->send('/foreign/group/invateMember', $data);
    }
    # 退出群聊
    public function outGroup($ToWxid, $chatroom):Response 
    {
        $data = array_merge($this->data, get_defined_vars());
        return $this->send('/foreign/group/outGroup', $data);
    }

    
}