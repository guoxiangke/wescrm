<?php

namespace App\Http\Livewire;

use App\Jobs\InitWechat;
use Livewire\Component;
use App\Services\Wechat;
use App\Models\WechatBot;
use App\Models\WechatBotContact;
use App\Models\WechatContact;
use App\Models\WechatContent;
use App\Models\WechatMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebChat extends Component
{

    public bool $isThread = false;
    public bool $isMobileShowContact = false;

    public bool $isCreating = true;
    public bool $isDarkUi = false;
    public  $conversions;
    public int $currentConversionId = 0;
    public bool $isRoom = false;
    
    public string $currentTeamName;
    public string $seatUserName = "专属客服";
    public string $seatUserAvatar = 'https://api.multiavatar.com/Kinestetic%20Moves.png';
    public bool $isEmojiPickerOpen = false;

    public WechatContent $wechatContent;
    public string $content = '';
    public WechatBot $wechatBot;
    public $wechatBotContacts;

    public string $defaultAvatar = WechatContact::DEFAULT_AVATAR; // fallback

    public function mount()
    {
        $user = auth()->user();
        
        $this->currentTeamName = $user->currentTeam->name;
        $this->seatUserName = $user->name;
        $this->seatUserAvatar = $user->profile_photo_url;

        $messages = WechatMessage::with(['contact', 'from', 'seat'])->orderBy('id', 'desc')->take(100)->get();
        // BadMethodCallException: Method Illuminate\Database\Eloquent\Collection::getKey does not exist. 
        // $this->conversions = $messages->groupBy('contact.id'); // not work

        // $this->conversions = collect($messages->groupBy('contact.id')); // works
        $this->conversions = $messages->groupBy('contact.id')->toArray(); //works
        // dd($this->conversions);
        $data = ['content'=>'请输入发送内容...'];
        $type = array_search('text', WechatContent::TYPES);
        $this->wechatContent = WechatContent::find(1);
        
        // new WechatContent([
        //     'name'=>'请输入发送内容...', 
        //     'type' => $type,
        //     'wechat_bot_id' => 1,
        //     'content'=>compact('data')
        // ]);
        
        
        $this->wechatBot = WechatBot::where('team_id', $user->currentTeam->id)->firstOrFail();
        // dd($this->wechatContent->toArray()); // has value
        // $this->wechatBotContacts = $this->contacts;//WechatBotContact::with('contact')->where('wechat_bot_id', $this->wechatBot->id)->where('type','>',0)->take(10)->get();
        // dd($this->wechatBotContacts->toArray());
        $this->wechatBotContacts = $this->contacts;
    }

    // $wxid or $wxids
    public function send()
    {
        // dd($this->conversions);
        if($this->currentConversionId){
            $wxids = $this->conversions[$this->currentConversionId][0]['contact']['userName'];
        }
        
        $data = ['content'=>$this->content];
        $content = compact('data');
        $this->wechatContent->content = $content;
        $wechatMessage = $this->wechatBot->send((array)$wxids, $this->wechatContent);
        // dd($wechatMessage->load(['contact', 'from', 'seat'])->toArray());
        array_unshift($this->conversions[$wechatMessage->conversation], $wechatMessage);
        $this->content = '';
    }

    public function updatedCurrentConversionId($value){
        $this->emit('scrollToEnd');
        $this->isCreating = false;
        $this->isMobileShowContact = false;
        // loading empty conversion
        if(!isset($this->conversions[$value])){
            $wechatMessage = new WechatMessage([
                'msgType'=>1,
                'wechat_bot_id'=>1,
                'conversation'=>$value,
                'from_contact_id'=>$value,
                'seat_user_id'=>null,
                'content' => ['content'=>'请在底部输入内容开始会话'],
                'updated_at' => now('Asia/Hong_Kong'),
            ]);
            $wechatMessage->load(['contact', 'from', 'seat']);
            $this->conversions[$value][] = $wechatMessage;
        }
        $this->isRoom = Str::endsWith($this->conversions[$this->currentConversionId][0]['contact']['userName'], '@chatroom')?true:false;
        $this->reset('search');
    }

 
    public $search = '';

    public function resetFilters()
    {
        $this->reset('search');
    }

    // protected $listeners = ['refreshContacts' => '$refresh'];


    public function updatedSearch($value){
        $this->wechatBotContacts = $this->contacts;
    }

    public function getContactsProperty()
    {
        return WechatBotContact::query()->with('contact')
            ->where('wechat_bot_id', $this->wechatBot->id)->where('type','>',0)
            ->when($this->search, fn($query, $search) => $query->where('remark', 'like', '%' . $search . '%'))
            ->take(10)
            ->get();
    }

    public function render()
    {
        // dd($this->contacts->toArray());
        return view('livewire.webchat', [
            'wechatBotContacts' => $this->contacts,
        ])->layout('layouts.webchat');
    }
}
