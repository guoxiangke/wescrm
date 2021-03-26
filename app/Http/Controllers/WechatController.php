<?php

namespace App\Http\Controllers;

use App\Models\WechatBot;
use App\Models\WechatContent;
use Illuminate\Http\Request;

class WechatController extends Controller
{

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function send(Request $request){
        // 1.check is live!
        // 2.发送权限 $this->authorize('send', $wechatBot); | 不用做了
        $wechatBot = WechatBot::where('team_id', auth()->user()->currentTeam->id)->firstOrFail();
        
        // {"type"=>'text', "to" =>"bluesky_still", "data": {"content": "API主动发送 文本/链接/名片/图片/视频 消息到好友/群"}}
        // { "type": "image", "to": "bluesky_still", "data": { "content": "https://www.upyun.com/static/logos/dangdang.png" } }
        // { "type": "video", "to": "bluesky_still", "data": { "path": "https://abc.yilindeli.com/teach/LevelTestMaterial/0zhumuTestFiles/test.mp4", "thumbPath": "https://www.upyun.com/static/logos/dangdang.png" } }
        // TODO url card !
        return $wechatBot->send(
            (array) $request['to'],
            WechatContent::make([
                'name' => 'tmpSendStructure',
                'type' => array_search($request['type'], WechatContent::TYPES),
                'content' => ['data'=>$request['data']],
            ])
        );
    }
    public function add(Request $request){
        $wechatBot = WechatBot::where('team_id', auth()->user()->currentTeam->id)->firstOrFail();
        return $wechatBot->addFriend($request['telephone'], $request['message']??"");
    }
}
