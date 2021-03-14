<?php

namespace App\Http\Livewire;

use App\Jobs\WechatInitQueue;
use Livewire\Component;
use App\Services\Wechat;
use App\Services\Weiju;
use App\Models\WechatBot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Membership;

class Weixin extends Component
{
    public $qr;
    public $showRemind;
    public $who;
    public $msg;
    public $teamName;
    public $expiresAt; // 当前bot的有效期
    public $allExpiresAt; //weiju token过期时间
    public $loginAt;
    

    public $wechatAutoReply;//boolean
    public $wechatListenRoom;
    public $wechatListenRoomAll;
    public $wechatListenGh;

    public $wechatTuingReply;//boolean
    public $wechatTulingKey;
    public $wechatTulingId;
    
    public $wechatWebhook;//boolean
    public $wechatWebhookUrl;
    public $wechatWebhookSecret;

    public $wechatWeiju;//boolean
    public $wechatWeijuWebhook;
    
    
    public function updated($name,$value)
    {
        if(in_array($name,['wechatWeiju','wechatWebhook','wechatWebhookUrl','wechatWebhookSecret','wechatTuingReply','wechatTulingKey','wechatTulingId','wechatAutoReply','wechatListenRoom','wechatListenRoomAll','wechatListenGh'])){
            $this->wechatBot->setMeta($name, $value);
        }
    }

    public function updatedWechatWeijuWebhook($value){
        if(filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)){ // url validate
            $this->wechatBot->setMeta('wechatWeijuWebhook', $value);
            $this->wechatBot->setCallBackUrl($value);
        }
    }

    // public $listened_rooms;
    public $wechatBot; // https://laravel-livewire.com/docs/2.x/properties
    // private $wechat; // protected and private properties DO NOT persist between Livewire updates. In general, you should avoid using them for storing state.
    public $wxid;  // 和前端部分相关的$Wxid全部改成小写$wxid @see WechatBot::getWxidAttribute();

    public function mount(Weiju $weiju)
    {
        $response = $weiju->getToken();
        if($response->failed()){
            $this->msg = "系统错误， 请刷新再试！";
            return ;
        }
        // dd($response->body());
        // "{"code":0,"msg":"登录的微信号已经超过数量限制！"}"
        // "{"code":1,"msg":"登录成功","num":2,"expiretime":"2021-03-02 11:21:16","data":{"apikey":"exxx
        
        // 显示 Team 关系
        // 用户必需属于一个team，且以此身份浏览 currentTeam
        /** @var $user \App\Models\User **/
        $user = auth()->user();
        $team = $user->currentTeam;
        $this->teamName = $team->name;

        // 付费管理2: 座席过期
        if(!$user->ownsTeam($team)){
            $membership = Membership::firstWhere(['team_id'=>$team->id,'user_id'=>$user->id]);
            // dd($membership->expires_at);
            if($membership->expires_at < now()){
                return $this->msg = "对不起，座席使用时间已过期，您暂时无法管理Bot，请与Bot管理员联系付费使用！";
            }
        }

        $wechatBot = WechatBot::firstwhere('team_id', $team->id);
        if($wechatBot){ //说明已经绑定过了！
            $this->wechatBot = $wechatBot;

            $this->wechatAutoReply = $wechatBot->getMeta('wechatAutoReply', false);
            $this->wechatListenRoom = $wechatBot->getMeta('wechatListenRoom', false);
            $this->wechatListenRoomAll = $wechatBot->getMeta('wechatListenRoomAll', false);
            $this->wechatListenGh = $wechatBot->getMeta('wechatListenGh', false); // 默认不接收 公众号消息
            
            $this->wechatTuingReply = $wechatBot->getMeta('wechatTuingReply', false);
            $this->wechatTulingKey = $wechatBot->getMeta('wechatTulingKey', '');
            $this->wechatTulingId = $wechatBot->getMeta('wechatTulingId', '');
            
            $this->wechatWebhook = $wechatBot->getMeta('wechatWebhook', false);
            $this->wechatWebhookUrl = $wechatBot->getMeta('wechatWebhookUrl', route('webhook.test'));
            $this->wechatWebhookSecret = $wechatBot->getMeta('wechatWebhookSecret', 'xxx');

            
            $this->wechatWeiju = $wechatBot->getMeta('wechatWeiju', false);
            $this->wechatWeijuWebhook = $wechatBot->getMeta('wechatWeijuWebhook', route('webhook.weiju'));
            
            // $this->listened_rooms = $wechatBot->getMeta('listened_rooms', ['xxxx@chatroom']); // 只接收少数几个群的群消息
            
            $this->wxid = $wechatBot->wxid;
            $wechat = new Wechat($this->wxid);
            // test 
            // $wechatBot->addBySearch('calebxyz')->json();

            $this->expiresAt = $wechatBot->expires_at->diffForHumans();
            $this->allExpiresAt = option('weiju.expired_at');
            $this->loginAt = optional($wechatBot->login_at)->diffForHumans();
            
            $responseWho = $wechat->who();
            // dd($responseWho->body()); // "{"code":-13,"msg":"您已退出微信","data":{}}"
            // {"code":1000,"msg":"成功","data":{"userName":"wxid_xx
            if($responseWho->ok() && $responseWho['code'] == 1000){ // 说明已经登录了
                $this->who = $responseWho['data'];
                // 付费管理1 bot过期 
                // TODO: schedule check! 如果用户一直不进这个页面，那么一直保持登录吗？！
                if($wechatBot->expired()){
                    $this->msg = "对不起，订阅到期，您暂时无法管理Bot，请与管理员联系付费使用！";
                    $wechat->send("sendText", ['ToWxid'=>'filehelper', 'content'=>$this->msg]);
                    $wechat->logout();
                }
                return ;
            }

        }

        if($response->failed()){
            $this->msg = "系统错误：".$response['msg']." ， # 请等待10分钟再试!";
            Log::error(__METHOD__, [__LINE__, $response]);
            return;
        }
        

        // 第一次登录流程：
            // 1. 判断是否获取到token or return Failed Msg 给管理后台.
            // 2. 获得token后，返回 到期时间，可登录微信数量 给管理后台
            // 3.
        if($response['code'] == 1) // && isset($response['data']['apikey'])
        {
            {// cache token
                // 使用option可靠存储，因为Cache可能失效！
                if(!option_exists('weiju.token')){
                    option(['weiju.token' => $response['data']['apikey']]);
                }
                $maxClientsCounts = $response['data']['num'];
                $expiresAt = $response['data']['expiretime'];

                $this->expiresAt = $expiresAt;
                option(['weiju.expired_at' => $expiresAt, 'weiju.clients' => $maxClientsCounts]);
            }
            // 如果weiju没有过期，且 还有剩余 可登录的bot配额
            ;
            if($expiresAt > now() && $maxClientsCounts > WechatBot::whereNotNull('login_at')->count()){
                $this->showRemind = true;
                // 若传入 wxid 将会弹窗登录,作为第二次登录参数，稳定不掉线
                $response = $this->wxid?$weiju->getQR($this->wxid):$weiju->getQR();
                // 返回二维码，默认上一行一定能成功
                $this->qr = $response['data']['qrCodeUrl']; // "http://weixin.qq.com/x/${wId}";
                // dd($response->json());
                $this->msg = $response['msg'] .  " 后台队列处理中，请按下面说明步骤操作，耐心等待3～4分钟后再刷新";
                // 启动后台队列任务，循环直到 扫码确认成功！
                Cache::put('weiju_wId', $response['data']['wId'], now()->addMinutes(5)); //防止多次刷新，多个Job
                WechatInitQueue::dispatch($response['data']['wId'], $team, $user->id)->delay(now()->addSecond(3));

                return ;
            }
            
        }

        // if($responseWho['code'] == -13){ // "{"code":-13,"msg":"您已退出微信","data":{}}"
        //     return $this->msg = "主动退出iPad登录后，需等5分钟再试!";
        // }
        Log::debug(__METHOD__, [$response]);
        // if($response['code'] == 0){
            return $this->msg = "主动退出iPad登录后，需等5分钟再试! 或" . $response['msg']; // "{"code":0,"msg":"登录的微信号已经超过数量限制！"}"
        // }
    }


    public function logout()
    {
        $wechat = new Wechat($this->wxid);
        $response = $wechat->logout();
        $this->wechatBot->update(['login_at'=>null]);
        $this->msg = "登出：" . $response['msg']. ", 请在手机上确认 退出iPad微信 即可。";

        $this->qr = null;
        $this->who = null;
        $this->wxid = null;
        $this->teamName = null;
    }

    public function render()
    {
        return view('livewire.weixin');
    }
}
