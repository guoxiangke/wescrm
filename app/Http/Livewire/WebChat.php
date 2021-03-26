<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\WechatBot;
use App\Models\WechatBotContact;
use App\Models\WechatContact;
use App\Models\WechatContent;
use App\Models\WechatMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class WebChat extends Component
{

    public string $defaultAvatar = WechatContact::DEFAULT_AVATAR; // fallback
    
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

    public function rules() { 
        return [
            'editing.name' => 'required|min:3',
            'editing.type' => 'required',
            'editing.wechat_bot_id' => 'required',
            'editing.content' => 'required',
        ];
    }

    
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
        $this->isDarkUi = $this->user->getMeta('isDarkUi', false);
        
    }
    public function updated($name,$value)
    {
        if(in_array($name,['isDarkUi'])){
            $this->user->setMeta($name, $value);
        }
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

    public $conversationFirstId = 0;
    public $loadMore = [];
    public function updatedCurrentConversationId($contactId){
        $this->hasNextPage =  true;
        $this->emit('scrollToEnd');
        $this->isCreating = false;
        $this->isMobileShowContact = false;

        // loading empty conversation
        $conversations = $this->wechatMessages;//->groupBy('contact.id')->toArray();
        if(!isset($conversations[$contactId])){
            $this->loadMore[$contactId] = false;
            $wechatMessage = new WechatMessage([
                'msgType'=>1,
                'wechat_bot_id'=>$this->wechatBot->id,
                'conversation'=>$contactId,
                'from_contact_id'=>$contactId,
                'seat_user_id'=>null,
                'content' => ['content'=>'请在底部输入内容开始会话'],
                'updated_at' => now('Asia/Hong_Kong'), // ？？for 切换新的会话
            ]);
            $this->wechatMessages[$contactId][] = $wechatMessage;
        }else{
            $this->conversationFirstId = current($conversations[$contactId])['id']??0; // ？？for 切换新的会话
        }
        
        if(isset($this->contacts[$contactId])){
            $this->isRoom = Str::endsWith($this->contacts[$contactId]['userName'], '@chatroom')?true:false;
        }else{
            //TODO 加载这个contact
            $this->isRoom = false;
            $newContact = WechatContact::where('id', $contactId)->get()->keyBy('id')->toArray();
            $this->contacts = $this->contacts + $newContact;
        }
        $this->reset('search');
        // $this->loadMore();
    }


    public $hideLoadMore = []; // by conversation
    public function loadMore()
    {
        $loadCount = 30;
        $messages = WechatMessage::where('conversation', $this->currentConversationId)
            ->where('wechat_bot_id', $this->wechatBot->id)
            ->where('id', '<', $this->conversationFirstId)
            ->take($loadCount)
            // ->orderBy('id', 'desc')
            ->get();
        $count = $messages->count();
        if($count>0){
            $this->conversationFirstId = $messages->last()->id;
            $old = $this->wechatMessages[$this->currentConversationId]??[];
            $old[$messages->id] = $messages->toArray();
            $this->wechatMessages[$this->currentConversationId] = $old;

            // 加载contacts
            if($this->isRoom){
                $contactIds = $messages->groupBy('from_contact_id')->keys()->filter();
                $addMoreIds = $contactIds->diff(collect($this->contacts)->keys());
                $contacts = WechatContact::whereIn('id', $addMoreIds)->get()->keyBy('id')->toArray();
                $this->contacts = $this->contacts + $contacts;
            }
        }

        if($count<$loadCount){
            //hide loadMore button! 最后一页
            $this->hideLoadMore[$this->currentConversationId] = true;
        }
    }

    public $searchIds;
    public $search = '';

    public function resetFilters()
    {
        $this->reset('search');
    }

    public function updatedSearch(){
        $value = trim($this->search);
        $addMoreIds = WechatBotContact::query()->with('contact')
            ->where('wechat_bot_id', $this->wechatBot->id)
            ->where('type','>',0)
            ->when($value, fn($query, $value) => $query->where('remark', 'like', '%' . $value . '%'))
            ->take(10)
            ->get()
            ->keyBy('wechat_contact_id');

        $contacts = WechatContact::whereIn('id', $addMoreIds->keys())->get()->keyBy('id')->toArray();
        
        $this->contacts = $this->contacts + $contacts;
        // dd($this->contacts, $addMoreIds->pluck('remark','wechat_contact_id')->toArray());
        $this->searchIds = $addMoreIds->pluck('remark','wechat_contact_id')->toArray();
    }

    public function getContacts(){
        return $this->contacts;
    }

    // protected $listeners = ['getNewMessages'];
    
    // public $conversations;
    // https://freek.dev/1622-replacing-websockets-with-livewire
    // public function getConversationsProperty() {
    //     // return $this->messages->load(['contact', 'from', 'seat'])->groupBy('contact.id')->toArray();
    //     return 1;
    // }
    public $contacts;
    public $seatUsers;
    public $wechatMessages;
    public int $maxMessageId = 0;

    // public function getWechatMessages(){
    //     return $this->conversations + $this->wechatMessages;
    // }

    public function getConversations(){
        if($this->maxMessageId!=0){
            Log::debug(__METHOD__, ['refresh', "maxMessageId:{$this->maxMessageId}", ]);
            $messages = WechatMessage::where('id', '>', $this->maxMessageId)
                ->where('wechat_bot_id', $this->wechatBot->id)
                ->get();
            if($messages->count()){
                // 新增加的 contacts 好友信息
                $conversationIds = $messages->groupBy('conversation')->keys();
                $fromIds = $messages->groupBy('from_contact_id')->keys()->filter();
                $contactIds =  $conversationIds->merge($fromIds)->unique();
                if($contactIds->count()){
                    Log::debug(__METHOD__, ['新消息中，包含的 contactIds', $contactIds->toArray()]);
                    // $this->contacts->keyBy('id')->toArray();
                    $addMoreIds = $contactIds->diff(collect($this->contacts)->keys());
                    Log::debug(__METHOD__, ['新消息中，包含的 addMoreIds', count($this->contacts), $addMoreIds->toArray()]);
                    if($addMoreIds->count()){
                        $contacts = WechatContact::whereIn('id', $addMoreIds->all())->get();
                        $this->contacts = $this->contacts + $contacts->keyBy('id')->toArray();
                        Log::debug(__METHOD__, ['新消息中 新增的 contacts', count($this->contacts), $contacts->toArray()]);
                    }
                }

                // $this->maxMessageId = $messages->last()->id;
                $this->maxMessageId = optional($messages->last())->id??$this->maxMessageId;
                // $messages = $this->wechatMessages->merge($messages);
                $messages->each(function($message) {
                    $old = $this->wechatMessages[$message->conversation]??[];
                    array_unshift($old, $message->toArray());
                    $this->wechatMessages[$message->conversation] = $old;
                    if($this->currentConversationId == $message->conversation){
                        $this->emit('scrollToEnd');
                    }
                });
            }
        }else{
            Log::debug(__METHOD__, ['init']);
            //初始化
            $messages = WechatMessage::where('wechat_bot_id', $this->wechatBot->id)
                ->where('created_at','>=', now()->subDays(7))
                ->get();
            $this->wechatMessages = $messages->groupBy('conversation')->map(fn($items)=>$items->keyBy('id'))->toArray();
            $this->maxMessageId = optional($messages->last())->id??-1;
            
            // contacts 好友信息
            $conversationIds = $messages->groupBy('conversation')->keys();
            $fromIds = $messages->groupBy('from_contact_id')->keys()->filter();
            $contactIds =  $conversationIds->merge($fromIds)->unique()->all();
            $this->contacts = WechatContact::whereIn('id', $contactIds)->get()->keyBy('id')->toArray();

            $this->seatUsers = $this->user->currentTeam->allUsers()->keyBy('id')->toArray();


            // search
            $this->updatedSearch();
        }
        // info($this->wechatMessages, [count($this->wechatMessages[518])]);
        Log::debug(__METHOD__, ['keys', array_keys($this->wechatMessages[518])]);
        return $this->wechatMessages;
    }
    public function render()
    {
        $conversations = $this->getConversations();
        // dd($conversations);
        $contactsArray = $this->getContacts();
        return view('livewire.webchat',[
            'conversations' => $conversations,
            'contactsArray' => $contactsArray // 不能直接 用 $this->contacts;
            ])->layout('layouts.webchat');
    }
}
