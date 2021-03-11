<?php

namespace App\Http\Livewire;

use App\Jobs\InitWechat;
use Livewire\Component;
use App\Services\Wechat;
use App\Services\Weiju;
use App\Models\WechatBot;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebChat extends Component
{

    public function mount(Weiju $weiju)
    {
    }


    public function render()
    {
        return view('livewire.webchat');
    }
}
