<?php

namespace App\Http\Livewire;

use App\Jobs\InitWechat;
use Livewire\Component;
use App\Services\Wechat;
use App\Services\Weiju;
use App\Models\WechatBot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Weixin extends Component
{
    public $qr;
    public $who;
    public $msg;
    public $teamName;
    // public $teamId; //TODO private!! 否则 会有安全风险
    // private $wechatBot; // https://laravel-livewire.com/docs/2.x/properties
    // private $wechat; // protected and private properties DO NOT persist between Livewire updates. In general, you should avoid using them for storing state.
    public $wxid;  // 和前端部分相关的$Wxid全部改成小写$wxid @see WechatBot::getWxidAttribute();

    public function mount(Weiju $weiju)
    {
        $response = $weiju->getToken();
        if($response->failed()){
            $this->msg = "系统错误， 请刷新再试！";
            return ;
        }
        // dd($response->body());// "{"code":0,"msg":"登录的微信号已经超过数量限制！"}"
        
        // 显示 Team 关系
        // 用户必需属于一个team，且以此身份浏览 currentTeam
        $user = auth()->user();
        $team = $user->currentTeam;
        $this->teamId = $team->id;
        $this->teamName = $team->name;

        $wechatBot = WechatBot::firstwhere('team_id', $this->teamId);
        if($wechatBot){ //说明已经绑定过了！
            $this->wxid = $wechatBot->wxid;
            $wechat = new Wechat($this->wxid);
            // test 
            // $wechatBot->addBySearch('calebxyz')->json();
        }
        if($response['code'] == 0) { // {"code":0,"msg":"登录的微信号已经超过数量限制！"}
            if(isset($wechat)){
                // // 你要实时自己调心跳接口
                // $response = $wechat->isOnline();
                // Log::debug(__METHOD__, ["isOnline", $response]);
                
                $response = $wechat->who();
                // dd($response->body()); // "{"code":-13,"msg":"您已退出微信","data":{}}"
                if($response->ok() && $response['code'] == 1000){ // 说明已经登录了
                    $this->who = $response['data'];
                }else{
                    $this->msg = "您已主动退出iPad登录，需要等待5分钟后再试!";
                    Log::debug(__METHOD__, ["失败", $response]);
                }
            }else{
                Log::debug(__METHOD__, ["失败", $response]);
                $this->msg = "Error:初始化失败！可能的原因：1.您无权限绑定（切换到bot所在的Team），2.请确认手机微信已退出iPad登录 后，等待3分钟再次刷新!";
            }
            return;
        }

        // 第一次登录流程：
            // 1. 判断是否获取到token or return Failed Msg 给管理后台.
            // 2. 获得token后，返回 到期时间，可登录微信数量 给管理后台
            // 3.

        $currentLogedInClientsCount = 0; //TODO

        //TODO 判断是否还有可登录Client的额度（付费是否过期）
    
        if($response->ok() && $response['code'] == 1)
        {

            // cache token
            if(isset($response['data']['apikey'])) {
                $token = $response['data']['apikey'];
                // 使用option可靠存储，因为Cache可能失效！
                if(!option_exists('weiju.token')){
                    option(['weiju.token' => $token]);
                }
                $ClientsCounts = $response['data']['num'];
                $ExpireAt = $response['data']['expiretime'];

                option(['weiju.expired_at' => $ExpireAt]); //TODO Show in UI and check!
            }

            
            if($ClientsCounts > $currentLogedInClientsCount && $ExpireAt > now()){
                // 若传入 wxid 将会弹窗登录,作为第二次登录参数，稳定不掉线
                $response = $this->wxid?$weiju->getQR($this->wxid):$weiju->getQR();

                $this->msg = "1.拿起手机2.打开手机微信3.首次绑定需扫码4.绑定后再次登录手机微信会收到弹窗5.在“iPad微信登录确认页面”点击登录5.耐心等待手机微信顶部显示“iPad登录”后 6.系统将进入后台初始化数据阶段，此过程需要3～5分钟，请等待5分钟后再刷新本页";
                // 启动后台队列任务，循环直到 扫码确认成功！
                
                Cache::put('weiju_wId', $response['data']['wId'], now()->addMinutes(5));
                InitWechat::dispatch($response['data']['wId'], $team)->delay(now()->addSecond(3));

                // 返回二维码
                $this->qr = $response['data']['qrCodeUrl'];
                // "http://weixin.qq.com/x/${wId}";
                return ;
            }
            
        }
        
        $this->msg = "系统错误：".$response['msg']." ， # 请等待10分钟再试!";
    }


    public function logout()
    {
        $wechat = new Wechat($this->wxid);
        $response = $wechat->who();
        $this->msg = "登出：" . $response['msg']. "请在手机上确认 退出iPad微信 即可。";

        $this->qr = null;
        $this->who = null;
        $this->wxid = null;
        $this->teamName = null;
    }

    public function send()
    {
        $user = auth()->user();
        $team = $user->currentTeam;
        $wechatBot = WechatBot::firstwhere('team_id', $team->id);

        $Wxid = "filehelper"; // 文件传输助手
        $room = '5829025039@chatroom';
        
        $content = "主动发送 文本/链接/名片/图片/视频 消息到好友/群";
        $wechatBot->send($Wxid, $content);
        $response = $wechatBot->send($room, $content);
        $this->msg = $response['msg'];
        
        $url = ['title'=>'测试链接到百度', 'url'=>'https://weibo.com', 'description'=>'其实我放的是微博的链接', 'thumbUrl'=>"https://www.bing.com/th?id=OHR.PeritoMorenoArgentina_ZH-CN8205335022_1920x1080.jpg"];
        $wechatBot->send($Wxid, $url);
        $wechatBot->send($room, $url);

        $card = ['nameCardId'=>$Wxid, 'nickName'=>'nothing'];
        $wechatBot->send($Wxid, $card);
        $wechatBot->send($room, $card);

        $image = "https://www.bing.com/th?id=OHR.PeritoMorenoArgentina_ZH-CN8205335022_1920x1080.jpg";
        $wechatBot->send($Wxid, $image);
        $wechatBot->send($room, $image);
        
        $video = ['path'=>"https://abc.yilindeli.com/teach/LevelTestMaterial/0zhumuTestFiles/test.mp4", 'thumbPath'=>"https://www.bing.com/th?id=OHR.PeritoMorenoArgentina_ZH-CN8205335022_1920x1080.jpg"];
        $wechatBot->send($Wxid, $video);
        $wechatBot->send($room, $video);

    }

    public function render()
    {
        return view('livewire.weixin');
    }
}
