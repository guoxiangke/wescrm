<?php

namespace App\Http\Livewire;

use App\Jobs\InitWechat;
use Livewire\Component;
use App\Services\Wechat;
use App\Models\User;
use App\Models\WechatBot;
use App\Models\WechatBotContact;
use App\Models\WechatContact;
use App\Models\WechatContent;
use App\Models\WechatMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Resources\WechatMessageResource;


class WebChat extends Component
{

    public bool $isThread = false;
    public bool $isMobileShowContact = false;

    public bool $isCreating = true;
    public bool $isDarkUi = false;
    public int $currentConversationId = 0;
    public bool $isRoom = false;
    
    public string $currentTeamName;
    public bool $isEmojiPickerOpen = false;

    public User $user;
    public WechatContent $editing;
    public string $content = '';
    public WechatBot $wechatBot;
    public $wechatBotContacts;

    public function rules() { 
        return [
            'editing.name' => 'required|min:3',
            'editing.type' => 'required',
            'editing.wechat_bot_id' => 'required',
            'editing.content' => 'required',
        ];
    }

    // public $conversations;
    // https://freek.dev/1622-replacing-websockets-with-livewire
    // public function getConversationsProperty() {
    //     // return $this->messages->load(['contact', 'from', 'seat'])->groupBy('contact.id')->toArray();
    //     return 1;
    // }

    public int $maxMessageId = 0;
    public function mount()
    {
        $this->user = auth()->user();
        $this->wechatBot = WechatBot::where('team_id', $this->user->currentTeam->id)->firstOrFail();

        $data = ['content'=>'请输入发送内容...'];
        $type = array_search('text', WechatContent::TYPES);
        $this->editing = new WechatContent([
            'name'=>'请输入发送内容...', 
            'type' => $type,
            'wechat_bot_id' => 1,
            'content'=>compact('data')
        ]);

        // $this->messages = WechatMessage::orderBy('id', 'desc')->take(100)->get();
        // BadMethodCallException: Method Illuminate\Database\Eloquent\Collection::getKey does not exist. 
        // https://github.com/livewire/livewire/issues/1054
        // $this->conversations = $messages->groupBy('contact.id'); // not work
        // $this->conversations = collect($messages->groupBy('contact.id')); // works
        // dd($this->conversations);
        
    }

    // $wxid or $wxids
    public function send()
    {
        $conversations = $this->wechatMessages;

        if($this->currentConversationId){
            $wxids = $this->contacts[$conversations[$this->currentConversationId][0]['conversation']]['userName'];
        }
        
        $data = ['content'=>$this->content];
        $content = compact('data');
        $this->editing->content = $content;
        $this->wechatBot->send((array)$wxids, $this->editing);

        $this->emit('refreshSelf');

        //$wechatMessage = $this->wechatBot->send((array)$wxids, $this->editing);
        // array_unshift($this->conversations[$wechatMessage->conversation], $wechatMessage);
        // $this->messages->prepend($wechatMessage);
        $this->content = '';
    }

    public function updatedCurrentConversationId($value){
        $this->emit('scrollToEnd');
        $this->isCreating = false;
        $this->isMobileShowContact = false;
        // dd($this->conversations); //0

        // loading empty conversation
        $conversations = $this->wechatMessages;//->groupBy('contact.id')->toArray();
        // dd($conversations);
        // $this->conversations = $conversations;
        if(!isset($conversations[$value])){
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
            // $this->conversations[] = $wechatMessage;
        }
        $contactId = $conversations[$value][0]['conversation'];
        // dd( $conversations[$value][0], $contactId , $this->contacts);
        $this->isRoom = Str::endsWith($this->contacts[$contactId]['userName'], '@chatroom')?true:false;
        $this->reset('search');
    }

 
    public $search = '';

    public function resetFilters()
    {
        $this->reset('search');
    }

    // protected $listeners = ['getNewMessages'];
    // public $messages;
    // public $wechatMessages;

    public $contacts;
    public $seatUsers;
    public $wechatMessages;
    public function getConversations(){
        if($this->maxMessageId!=0){
            $messages = WechatMessage::where('id', '>', $this->maxMessageId)
                ->orderBy('id', 'desc')
                ->get()
                ->keyBy('id');
        
            if($messages->count()){
                $this->maxMessageId = $messages->first()->id;
                // $messages = $this->wechatMessages->merge($messages);
                
                $messages->each(function($message) {
                    $old = $this->wechatMessages[$message->conversation]??[];
                    array_unshift($old, $message->toArray());
                    $this->wechatMessages[$message->conversation] = $old;
                });

                // 新增加的 contacts 好友信息
                $conversationIds = $messages->groupBy('conversation')->keys();
                $fromIds = $messages->groupBy('from_contact_id')->keys()->filter();
                $contactIds =  $conversationIds->merge($fromIds)->unique();
                if($contactIds->count()){
                    Log::error(__METHOD__, ['新消息中，包含的 contactIds', $contactIds->toArray()]);
                    // $this->contacts->keyBy('id')->toArray();
                    $addMoreIds = $contactIds->diff(collect($this->contacts)->keys());
                    Log::error(__METHOD__, ['新消息中，包含的 addMoreIds', count($this->contacts), $addMoreIds->toArray()]);
                    if($addMoreIds->count()){
                        $contacts = WechatContact::whereIn('id', $addMoreIds->all())->get();
                        $contacts->each(fn($contact) => $this->contacts[$contact->id] = $contact->toArray());

                        Log::error(__METHOD__, ['新消息中 新增的 contacts', count($this->contacts), $contacts->toArray()]);
                    }

                    // $this->contacts = $this->contacts->merge(WechatContact::whereIn('id', $contactIds->all())->get());
                }
                
                $this->emit('scrollToEnd');
                // $this->wechatMessages = $messages;
                // info(['getConversations1=', $this->maxMessageId, $messages->count(), $this->wechatMessages->count()]);
            }
            Log::debug(__METHOD__, ['refresh in render', 'keys', array_keys($this->wechatMessages)]);
            // Log::debug(__METHOD__, ['refresh-render', 'maxMessageId', $this->maxMessageId, array_keys($this->wechatMessages)]);
            // Log::debug(__METHOD__, ['refresh-render', 'maxMessageId', $this->maxMessageId, array_keys($this->wechatMessages)]);
            return $this->wechatMessages;//$wechatMessages->groupBy('conversation')->toArray();//->groupBy('conversation')->toArray();
        }else{
            //初始化
            $messages = WechatMessage::latest() //with(['seat','contact','from'])
                ->take(100)
                ->get()
                ->keyBy('id');
            
            $this->maxMessageId = $messages->first()->id;

            // TODO by team 座席用户信息
            $seatUserIds = $messages->groupBy('seat_user_id')->keys()->filter();
            $this->seatUsers = User::whereIn('id', $seatUserIds)->get()->keyBy('id')->toArray();
                
            // contacts 好友信息
            $conversationIds = $messages->groupBy('conversation')->keys();
            $fromIds = $messages->groupBy('from_contact_id')->keys()->filter();
            $contactIds =  $conversationIds->merge($fromIds)->unique()->all();
            $this->contacts = WechatContact::whereIn('id', $contactIds)->get()->keyBy('id')->toArray();
            
            $this->wechatMessages = $messages->groupBy('conversation')->toArray();
            Log::debug(__METHOD__,['init called']);
        }
        Log::debug(__METHOD__, ['keys', array_keys($this->wechatMessages)]);
        Log::debug(__METHOD__, ['count', count($this->wechatMessages)]);
        // Log::debug(__METHOD__, ['contacts', count($this->contacts)]);
        Log::debug(__METHOD__, ['seatUsers', count($this->seatUsers)]);
        // dd($this->seatUsers);
        return $this->wechatMessages;
    }

    public function updatedSearch($value){
        $this->wechatBotContacts = WechatBotContact::query()->with('contact')
            ->where('wechat_bot_id', $this->wechatBot->id)->where('type','>',0)
            ->when($this->search, fn($query, $search) => $query->where('remark', 'like', '%' . $search . '%'))
            ->take(10)
            ->get();
    }

    public function render()
    {
        $conversations = $this->getConversations();
        return view('livewire.webchat',['conversations'=>$conversations])->layout('layouts.webchat');
    }
}
