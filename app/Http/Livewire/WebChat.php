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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class WebChat extends Component
{

    public string $defaultAvatar = WechatContact::DEFAULT_AVATAR; // fallback
    
    public bool $isThread; //是否开启右边栏
    public bool $isEmojiPicker;
    public bool $isMobileShowContact = false;

    public bool $isCreating = true;
    public bool $isDarkUi = false;
    public int $currentConversationId = 0;
    public bool $isRoom = false;
    
    public string $currentTeamName;

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

    
    public $seatUsers;
    public $emojis;
    public $codes;
    public $styles;
    public $remarks;
    public function mount()
    {

        $this->isThread = true;
        $this->isEmojiPicker = false;

        $this->codes = json_decode('{"0":"[\u5fae\u7b11]","1":"[\u6487\u5634]","2":"[\u8272]","3":"[\u53d1\u5446]","4":"[\u5f97\u610f]","5":"[\u6d41\u6cea]","6":"[\u5bb3\u7f9e]","7":"[\u95ed\u5634]","8":"[\u7761]","9":"[\u5927\u54ed]","10":"[\u5c34\u5c2c]","11":"[\u53d1\u6012]","12":"[\u8c03\u76ae]","13":"[\u5472\u7259]","14":"[\u60ca\u8bb6]","15":"[\u96be\u8fc7]","17":"[\u51b7\u6c57]","18":"[\u6293\u72c2]","19":"[\u5410]","20":"[\u5077\u7b11]","21":"[\u6109\u5feb]","22":"[\u767d\u773c]","23":"[\u50b2\u6162]","25":"[\u56f0]","26":"[\u60ca\u6050]","27":"[\u6d41\u6c57]","28":"[\u61a8\u7b11]","29":"[\u60a0\u95f2]","30":"[\u594b\u6597]","31":"[\u5492\u9a82]","32":"[\u7591\u95ee]","33":"[\u5618]","34":"[\u6655]","36":"[\u8870]","37":"[\u9ab7\u9ac5]","38":"[\u6572\u6253]","39":"[\u518d\u89c1]","40":"[\u64e6\u6c57]","41":"[\u62a0\u9f3b]","42":"[\u9f13\u638c]","44":"[\u574f\u7b11]","45":"[\u5de6\u54fc\u54fc]","46":"[\u53f3\u54fc\u54fc]","47":"[\u54c8\u6b20]","48":"[\u9119\u89c6]","49":"[\u59d4\u5c48]","50":"[\u5feb\u54ed\u4e86]","51":"[\u9634\u9669]","52":"[\u4eb2\u4eb2]","54":"[\u53ef\u601c]","55":"[\u83dc\u5200]","56":"[\u897f\u74dc]","57":"[\u5564\u9152]","60":"[\u5496\u5561]","62":"[\u732a\u5934]","63":"[\u73ab\u7470]","64":"[\u51cb\u8c22]","65":"[\u5634\u5507]","66":"[\u7231\u5fc3]","67":"[\u5fc3\u788e]","68":"[\u86cb\u7cd5]","70":"[\u70b8\u5f39]","74":"[\u4fbf\u4fbf]","75":"[\u6708\u4eae]","76":"[\u592a\u9633]","78":"[\u62e5\u62b1]","79":"[\u5f3a]","80":"[\u5f31]","81":"[\u63e1\u624b]","82":"[\u80dc\u5229]","83":"[\u62b1\u62f3]","84":"[\u52fe\u5f15]","85":"[\u62f3\u5934]","89":"[OK]","92":"[\u8df3\u8df3]","93":"[\u53d1\u6296]","94":"[\u6004\u706b]","95":"[\u8f6c\u5708]","300":"[\u7b11\u8138]","301":"[\u751f\u75c5]","302":"[\u7834\u6d95\u4e3a\u7b11]","303":"[\u5410\u820c]","304":"[\u8138\u7ea2]","305":"[\u6050\u60e7]","306":"[\u5931\u671b]","307":"[\u65e0\u8bed]","204":"[\u563f\u54c8]","205":"[\u6342\u8138]","202":"[\u5978\u7b11]","206":"[\u673a\u667a]","212":"[\u76b1\u7709]","211":"[\u8036]","313":"[\u5403\u74dc]","314":"[\u52a0\u6cb9]","315":"[\u6c57]","316":"[\u5929\u554a]","317":"[Emm]","318":"[\u793e\u4f1a\u793e\u4f1a]","319":"[\u65fa\u67f4]","320":"[\u597d\u7684]","321":"[\u6253\u8138]","322":"[\u54c7]","308":"[\u9b3c\u9b42]","309":"[\u5408\u5341]","310":"[\u5f3a\u58ee]","311":"[\u5e86\u795d]","312":"[\u793c\u7269]","209":"[\u7ea2\u5305]"}',true);
        $this->styles = json_decode('{"0":"smiley_0","1":"smiley_1","2":"smiley_2","3":"smiley_3","4":"smiley_4","5":"smiley_5","6":"smiley_6","7":"smiley_7","8":"smiley_8","9":"smiley_9","10":"smiley_10","11":"smiley_11","12":"smiley_12","13":"smiley_13","14":"smiley_14","15":"smiley_15","17":"smiley_17","18":"smiley_18","19":"smiley_19","20":"smiley_20","21":"smiley_21","22":"smiley_22","23":"smiley_23","25":"smiley_25","26":"smiley_26","27":"smiley_27","28":"smiley_28","29":"smiley_29","30":"smiley_30","31":"smiley_31","32":"smiley_32","33":"smiley_33","34":"smiley_34","36":"smiley_36","37":"smiley_37","38":"smiley_38","39":"smiley_39","40":"smiley_40","41":"smiley_41","42":"smiley_42","44":"smiley_44","45":"smiley_45","46":"smiley_46","47":"smiley_47","48":"smiley_48","49":"smiley_49","50":"smiley_50","51":"smiley_51","52":"smiley_52","54":"smiley_54","55":"smiley_55","56":"smiley_56","57":"smiley_57","60":"smiley_60","62":"smiley_62","63":"smiley_63","64":"smiley_64","65":"smiley_65","66":"smiley_66","67":"smiley_67","68":"smiley_68","70":"smiley_70","74":"smiley_74","75":"smiley_75","76":"smiley_76","78":"smiley_78","79":"smiley_79","80":"smiley_80","81":"smiley_81","82":"smiley_82","83":"smiley_83","84":"smiley_84","85":"smiley_85","89":"smiley_89","92":"smiley_92","93":"smiley_93","94":"smiley_94","95":"smiley_95","300":"u1F604","301":"u1F637","302":"u1F602","303":"u1F61D","304":"u1F633","305":"u1F631","306":"u1F614","307":"u1F612","204":"e2_04","205":"e2_05","202":"e2_02","206":"e2_06","212":"e2_12","211":"e2_11","313":"smiley_313","314":"smiley_314","315":"smiley_315","316":"smiley_316","317":"smiley_317","318":"smiley_318","319":"smiley_319","320":"smiley_320","321":"smiley_321","322":"smiley_322","308":"u1F47B","309":"u1F64F","310":"u1F4AA","311":"u1F389","312":"u1F381","209":"e2_09"}',true);
        
        // dd($codes, $styles);

        $this->user = auth()->user();
        $this->seatUsers = $this->user->currentTeam->allUsers()->keyBy('id')->toArray();
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
        $this->emojis = ['👍🏼','👎','✌','🤝','💪','✊','🙏','👌','👋','🤟','😀','😂','🤫','😵','🤗','😘','😍','🥺','😆','😅','😥','😓','😐','🤭','🤥','😱','😷','😠','🤕','😲','😔','😗','😝','🙇','🧧','💔','🍉','☕','🍺','🐞','🎉','🎁','👻'];
        $this->attach = false;

        $this->remarks = WechatBotContact::where('wechat_bot_id', $this->wechatBot->id)->pluck('remark','wechat_contact_id')->toArray();
    }
    public function insertEmoji($emoji)
    {   
        $this->content .= $emoji;
    }
    public function updated($name, $value)
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
            $wxid = $this->contacts[current($conversations[$this->currentConversationId])['conversation']]['userName'];
        }
        
        if($this->file){ //begin发送文件
            // new temp wechatContent for send;
            $wechatContent = new WechatContent([
                'name' => 'send Attachment',
                'wechat_bot_id' => $this->wechatBot->id,
            ]);
            // $type = $this->file->getMimeType();
            $fileName = $this->file->getFilename();
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            switch ($extension) {
                case 'png':
                case 'jpeg':
                case 'jpg':
                case 'gif':
                    $wechatContent->type = WechatContent::TYPE_IMAGE;
                    $wechatContent->content = [
                        'content'=> $this->file->temporaryUrl()
                    ];
                    $this->wechatBot->send($wxid, $wechatContent);
                    return;
                    break;
                case 'mp4':
                    $wechatContent->type = WechatContent::TYPE_VIDEO;
                    $wechatContent->content = [
                        'path'=> $this->file->temporaryUrl(),
                        'thumbPath'=> 'https://www-static.qbox.me/_next/static/media/banner.62cd5d22423097531dfdc2223bbc4c97.png',
                    ];
                    break;
                default: //others
                    $fileName = $this->file->getFilename();
                    $this->file->storeAs('public/files', $fileName);
                    $link =  config('app.url') . Storage::url('public/files/'.$fileName);

                    $originName = str_replace(".$extension", '', $this->file->getClientOriginalName());
                    $wechatContent->type = WechatContent::TYPE_TEXT;
                    $content = "您有收到{$extension}文件\r\n文件名：{$originName}\r\n点此查看：{$link}";
                    $wechatContent->content = [
                        'content'=> $content,
                    ];
                    break;
            }
            $this->wechatBot->send($wxid, $wechatContent);
            $this->reset('file');
        }// end发送文件

        if(trim($this->content)=='') return; //TODO 空消息 不提交
        $this->editing->type = WechatContent::TYPE_TEXT;
        $content = ['content'=>$this->content];
        // $content = compact('data');
        $this->editing->content = $content;
        $this->wechatBot->send($wxid, $this->editing);

        $this->emit('refreshSelf');
        
        $this->content = '';
        $this->isEmojiPicker = false;
    }

    use WithFileUploads;
    public $file;
    public $attach;
    public function updatedFile($value){
        $this->attach = $value->getClientOriginalName();
        // https://laravel.com/docs/8.x/validation#basic-usage-of-mime-rule
        // https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
        $this->validate([
            //.jpg,.png,.gif,.mp4,.pdf,.doc,.docx,.txt,.md,.pptx,.ppt,.zip
            'file' => 'mimes:jpg,png,jpeg,gif,mp4,mp4v,mpg4,m4a,pdf,doc,docx,ppt,pptx,zip,txt,md|max:64',
        ]);
    }
    public function resetFile(){
        $this->reset('file');
    }
    

    public $conversationFirstId = 0;
    public $loadMore = [];
    public function updatedCurrentConversationId($contactId){
        $this->isThread = false;
        $this->content = ''; //清空内容
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
            ->get();
        $count = $messages->count();
        if($count>0){
            $this->conversationFirstId = $messages->last()->id;
            $old = $this->wechatMessages[$this->currentConversationId]??[];
            $new = [];
            $messages->each(function($message) use(&$new){
                $new[$message->id] = $message->toArray();
            });
            $this->wechatMessages[$this->currentConversationId] = $new + $old;

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
    public $lastMessageContacts;
    public $wechatMessages;
    public int $maxMessageId = 0;
    
    public function getConversations(){
        if($this->maxMessageId!=0){
            // Log::debug(__METHOD__, ['refresh', "maxMessageId:{$this->maxMessageId}", ]);
            $messages = WechatMessage::where('id', '>', $this->maxMessageId)
                ->where('wechat_bot_id', $this->wechatBot->id)
                ->get();
            if($messages->count()){
                // 新增加的 contacts 好友信息
                $conversationIds = $messages->groupBy('conversation')->keys();
                $fromIds = $messages->groupBy('from_contact_id')->keys()->filter();
                $contactIds =  $conversationIds->merge($fromIds)->unique();
                if($contactIds->count()){
                    // Log::debug(__METHOD__, ['新消息中，包含的 contactIds', $contactIds->toArray()]);
                    // $this->contacts->keyBy('id')->toArray();
                    $addMoreIds = $contactIds->diff(collect($this->contacts)->keys());
                    // Log::debug(__METHOD__, ['新消息中，包含的 addMoreIds', count($this->contacts), $addMoreIds->toArray()]);
                    if($addMoreIds->count()){
                        $contacts = WechatContact::whereIn('id', $addMoreIds->all())->get();
                        $this->contacts = $this->contacts + $contacts->keyBy('id')->toArray();
                        // Log::debug(__METHOD__, ['新消息中 新增的 contacts', count($this->contacts), $contacts->toArray()]);
                    }
                }

                // $this->maxMessageId = $messages->last()->id;
                $this->maxMessageId = optional($messages->last())->id??$this->maxMessageId;
                // $messages = $this->wechatMessages->merge($messages);
                $messages->each(function($message) {
                    $old = $this->wechatMessages[$message->conversation]??[];
                    array_push($old, $message->toArray());
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
                ->where('created_at','>=', now()->subDays(3))
                ->orderBy('created_at','desc')
                ->limit(2000)
                ->get();
            $this->wechatMessages = $messages->groupBy('conversation')->map(fn($items)=>$items->keyBy('id'))->toArray();
            $this->maxMessageId = optional($messages->last())->id??-1;
            
            // contacts 好友信息
            $conversationIds = $messages->groupBy('conversation')->keys();
            $fromIds = $messages->groupBy('from_contact_id')->keys()->filter();
            $contactIds =  $conversationIds->merge($fromIds)->unique()->all();
            $this->contacts = WechatContact::whereIn('id', $contactIds)->get()->keyBy('id')->toArray();


            // search
            $this->updatedSearch();
        }
        // 按最后消息排序 contatcs
        $this->lastMessageContacts = [];
        foreach($this->wechatMessages as $conversation){
            $conversation = end($conversation); //最后一个对方发的消息
            $this->lastMessageContacts[] = $conversation;
        }
        $this->lastMessageContacts = collect($this->lastMessageContacts)->sortBy([['created_at','desc']])->toArray();
        // Log::debug(__METHOD__, ['keys', array_keys($this->wechatMessages)]);
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
