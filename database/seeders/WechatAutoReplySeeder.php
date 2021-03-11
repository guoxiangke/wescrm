<?php

namespace Database\Seeders;

use App\Models\WechatAutoReply;
use Illuminate\Database\Seeder;

class WechatAutoReplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (\App::environment('local')) {
                
            // hi* 代表以hi开头的
            // *hi   代表以hi结尾的
            // *hi* 代表包含hi的
            // *hi*thanks* 代表先hi后thanks的
            $contents = [
                'hi*'=>7,
                '*hi'=>8,
                '*hi*'=>9,
                '*hi*thanks*'=>10,
            ];
            foreach ($contents as $keyword => $contentId) {
                WechatAutoReply::create([
                    'keyword' => $keyword,
                    'wechat_bot_id' => 1,
                    'wechat_content_id' => $contentId,
                ]);
            }
        }
    }
}
