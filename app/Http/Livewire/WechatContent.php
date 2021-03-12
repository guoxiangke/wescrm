<?php

namespace App\Http\Livewire;

use App\Models\WechatBot;
use App\Models\WechatContent as Model;
use Livewire\Component;

use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Jobs\WechatBatchSendByTags;
use App\Rules\WechatContentRule;
use Spatie\Tags\Tag;

// 批量发送任务和记录（1次性群发）
class WechatContent extends Component
{
    public $state; // 从前端接收群发参数
    public $contents;
    public $contentId=0; // 选择发送内容id

    public $tagWith;
    public $tags;
    public $selectedTags=[];
    public $sendAt;

    public WechatBot $wechatBot;
    public function mount()
    {
        
        $currentTeamId = auth()->user()->currentTeam->id;
        $this->wechatBot = WechatBot::where('team_id', $currentTeamId)->firstOrFail();
        
        $this->contents = Model::where('wechat_bot_id', $this->wechatBot->id)->pluck('name','id');
        // dd(WechatBotContact::with('contact')->where(['wechat_bot_id' => $wechatBot->id, 'type'=>WechatContact::TYPES['friend']])->first()->toArray());
        $this->editing = $this->makeBlankModel();
        $this->sorts = ['updated_at'=>'desc']; //默认排序

        $this->tagWith =  'wechat-contact-team-'.$currentTeamId;
        $this->tags = Tag::getWithType($this->tagWith)->pluck('name');
    }
    // WechatContact::TYPES[$type], // 0:friend好友,1:group群,2:public公众号,3:非好友群成员


    public function render()
    {
        return view('livewire.wechat-content', ['models' => $this->rows]);
    }

    use WithPerPagePagination, WithSorting, WithBulkActions,  WithCachedRows;
    public $showEditModal = false;
    public $showBatchModal = false;
    public $model = Model::class;
    public $filters = [
        'search' => '',
    ];
    public Model $editing;

    protected $queryString = ['sorts'];

    protected $listeners = ['refreshWechatContents' => '$refresh'];

    public function rules()
    {
        return [
            'editing.name' => ['required', 'min:4'],
            'editing.content' => ['required', 'json', new WechatContentRule($this->editing->type)],
            'editing.type' => ['required', 'integer'],
            'editing.wechat_bot_id' => ['required', 'integer'], //default value.
        ];
    }

    public function makeBlankModel()
    {
        return Model::make(['wechat_bot_id' => $this->wechatBot->id]);
    }

    public function create()
    {
        $this->useCachedRows();

        if ($this->editing->getKey()) $this->editing = $this->makeBlankModel();

        $this->showEditModal = true;
    }

    public function testSend()
    {
        $wchatContent = Model::findOrFail($this->contentId);//TODO validate 必需是自己的内容
        $this->wechatBot->send(["filehelper"], $wchatContent);
    }

    //TODO two forms validate
    
    public function sendByTags()
    {
        $this->showBatchModal = false;
        if(!$this->contentId){
            $this->showBatchModal = true;
            //TODO Error:请选择发送内容
            return;
        }
        if(!$this->selectedTags){
            //TODO Error:请选择标签
            return;
        }
        $delayByMinutes = 0;
        if($this->sendAt){
            $hm = explode(':', $this->sendAt);
            $delayByMinutes = now()->diffInMinutes(now()->setHours($hm[0])->setMinutes($hm[1]));
        }
        // add to Queue
        $wechatContent = Model::findOrFail($this->contentId);//TODO validate 必需是自己的内容
        WechatBatchSendByTags::dispatch(
            $this->wechatBot,
            $wechatContent,
            $this->selectedTags,
            $this->tagWith
        )->delay(now()->addMinutes($delayByMinutes));
    }

    public function edit(Model $model)
    {
        $this->useCachedRows();

        if ($this->editing->isNot($model)) $this->editing = $model;
        $this->editing['content'] = json_encode($this->editing['content']['data']);

        $this->showEditModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = json_decode($this->editing->content, 1);
        
        $this->editing->content = compact('data'); // {"data": {"content": "主动发送 文本/链接/名片/图片/视频 消息到好友/群"}}

        $this->editing->save();

        $this->showEditModal = false;

        $this->dispatchBrowserEvent('notify', 'Saved!');
    }

    public function resetFilters()
    {
        $this->reset('filters');
    }

    public function getRowsQueryProperty()
    {
        $query = Model::query()
            ->when($this->filters['search'], fn($query, $search) => $query->where('name', 'like', '%' . $search . '%'));

        return $this->applySorting($query);
    }

    public function getRowsProperty()
    {
        return $this->cache(function () {
            return $this->applyPagination($this->rowsQuery);
        });
    }
}