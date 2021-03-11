<?php

namespace Database\Seeders;

use App\Models\WechatContent;
use Illuminate\Database\Seeder;

class WechatContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    { // For dev
        if (\App::environment('local')) {
                
            $content = "主动发送 文本/链接/名片/图片/视频 消息到好友/群";
            $contents[] = ['type'=>'text', 'content'=>['data'=>compact('content')]];

            $content = "send text with template :nickName";
            $contents[] = ['type'=>'template', 'content'=>['data'=>compact('content')]];
            
            $content = "https://www.upyun.com/static/logos/dangdang.png"; $image = $content;
            $contents[] = ['type'=>'image', 'content'=>['data'=>compact('content')]];
            
            $url = ['title'=>'测试链接到百度', 'url'=>'https://weibo.com', 'description'=>'其实我放的是微博的链接', 'thumbUrl'=>$image];
            $contents[] = ['type'=>'url', 'content'=>['data'=>$url]];

            $card = ['nameCardId'=>'bluesky_still', 'nickName'=>'nothing'];
            $contents[] = ['type'=>'card', 'content'=>['data'=>$card]];
            
            $video = ['path'=>"https://abc.yilindeli.com/teach/LevelTestMaterial/0zhumuTestFiles/test.mp4", 'thumbPath'=>$image];
            $contents[] = ['type'=>'video', 'content'=>['data'=>$video]];


            $content = "hi* 代表以hi开头的";
            $contents[] = ['type'=>'text', 'content'=>['data'=>compact('content')]];
            $content = "*hi   代表以hi结尾的";
            $contents[] = ['type'=>'text', 'content'=>['data'=>compact('content')]];
            $content = "*hi* 代表包含hi的";
            $contents[] = ['type'=>'text', 'content'=>['data'=>compact('content')]];
            $content = "*hi*thanks* 代表先hi后thanks的";
            $contents[] = ['type'=>'text', 'content'=>['data'=>compact('content')]];

            foreach ($contents as $content) {
                $type = array_search($content['type'], WechatContent::TYPES);
                WechatContent::create([
                    'name' => "测试". WechatContent::TYPES_CN[$type],
                    'wechat_bot_id' => 1,
                    'type' => $type,
                    'content' => $content['content'],
                ]);
            }
        }
    }
}
