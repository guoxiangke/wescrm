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
        if (true || \App::environment('local')) {
                
            $content = "主动发送 文本/链接/名片/图片/视频 消息到好友/群";
            $contents[] = ['type'=>'text', compact('content')];

            $content = "send text with template name  好友自己设置的昵称: :name 备注或昵称: :remark 客服座席名字: :seat 第:no号好友";
            $contents[] = ['type'=>'template', compact('content')];
            
            $content = "https://www.upyun.com/static/logos/dangdang.png"; $image = $content;
            $contents[] = ['type'=>'image', compact('content')];
            
            $url = ['title'=>'测试链接到百度', 'url'=>'https://weibo.com', 'description'=>'其实我放的是微博的链接', 'thumbUrl'=>$image];
            $contents[] = ['type'=>'url', $url];

            $card = ['nameCardId'=>'bluesky_still', 'nickName'=>'nothing'];
            $contents[] = ['type'=>'card', $card];
            
            $video = ['path'=>"https://abc.yilindeli.com/teach/LevelTestMaterial/0zhumuTestFiles/test.mp4", 'thumbPath'=>$image];
            $contents[] = ['type'=>'video', $video];


            $content = "hi* 代表以hi开头的";
            $contents[] = ['type'=>'text', compact('content')];
            $content = "*hi   代表以hi结尾的";
            $contents[] = ['type'=>'text', compact('content')];
            $content = "*hi* 代表包含hi的";
            $contents[] = ['type'=>'text', compact('content')];
            $content = "*hi*thanks* 代表先hi后thanks的";
            $contents[] = ['type'=>'text', compact('content')];

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
