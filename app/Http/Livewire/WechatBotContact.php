<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\WechatBotContact as Model;
// dd(WechatBotContact::with('contact')->where(['wechat_bot_id' => $wechatBot->id, 'type'=>WechatContact::TYPES['friend']])->first()->toArray());
use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Models\WechatBot;
use App\Models\WechatContact;
use Illuminate\Support\Facades\Log;
use Spatie\Tags\Tag;

use function PHPSTORM_META\map;

// 通讯录管理
class WechatBotContact extends Component
{
    public int $wechatBotId;

    public $tags;

    public string $defaultAvatar = WechatContact::DEFAULT_AVATAR;
    
    public function render()
    {
        $models = $this->rows;
        $tags = [];
        $models->each(function($model) use(&$tags){
            $model->tags->each(function($tag) use($model,&$tags){
                $tags[$model->id][$tag->id] = $tag->name;
            });
        });
        $this->tags = $tags;
        return view('livewire.wechat-bot-contact', compact('models'));
    }

    public function updateRemark(Model $wechatBotContact, $value){
        $wechatBotContact->remark = trim($value);
        if($wechatBotContact->isDirty('remark')){
            $wechatBotContact->save();
        }
    }

    use WithPerPagePagination, WithSorting, WithBulkActions, WithCachedRows;

    public $showDeleteModal = false;
    public $showEditModal = false;
    public $model = Model::class;
    public $filters = [
        'search' => '',
    ];
    public Model $editing;

    protected $queryString = ['sorts'];

    protected $listeners = ['refreshWechatContacts' => '$refresh'];

    public $editTag = '';
    public function rules()
    {
        return [
            'editing.remark' => 'required|min:3',
            'editing.seat_user_id' => 'required',
            // 'editing.tag' => 'sometimes',
        ];
    }

    public $tagWith;
    public function mount()
    {
        
        $currentTeamId = auth()->user()->currentTeam->id;
        $wechatBot = WechatBot::where('team_id', $currentTeamId)->firstOrFail();
        $this->wechatBotId = $wechatBot->id;
        $this->editing = $this->makeBlankModel();
        $this->tagWith =  'wechat-contact-team-'.$currentTeamId;
        // $tagA = Tag::findOrCreate('tagA', $this->tagWith);
        // $tagB = Tag::findOrCreate('tagB', $this->tagWith);
        // $this->tags = Tag::getWithType($this->tagWith)->pluck('name','id')->toArray();
        // dd($this->tags);
    }


    public function detachTag(Model $wechatBotContact, string $tagName)
    {
        $wechatBotContact->detachTag($tagName, $this->tagWith);
    }

    public function attachTag(Model $wechatBotContact, string $tagName)
    {
        $tagName = trim($tagName);
        if($tagName) {
            Tag::findOrCreate($tagName, $this->tagWith);
            $wechatBotContact->attachTag($tagName, $this->tagWith);
        }
        $this->editTag = '';
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }

    public function exportSelected()
    {
        return response()->streamDownload(function () {
            echo $this->selectedRowsQuery->toCsv();
        }, $this->editing->getTable() . '.csv');
    }

    public function deleteSelected()
    {
        $deleteCount = $this->selectedRowsQuery->count();

        $this->selectedRowsQuery->delete();

        $this->showDeleteModal = false;

        $this->notify('You\'ve deleted ' . $deleteCount . ' Models');
    }

    public function makeBlankModel()
    {
        return Model::make(['date' => now(), 'status' => 'success']);
    }

    public function edit(Model $model)
    {
        $this->useCachedRows();
        if ($this->editing->isNot($model)) $this->editing = $model;

        $this->showEditModal = true;
    }

    public function save()
    {
        $this->validate();
        $this->editing->save();
        if($this->editTag){
            $tagName = trim($this->editTag);
            if($tagName) {
                Tag::findOrCreate($tagName, $this->tagWith);
                $this->editing->attachTag($tagName, $this->tagWith);
                $this->editTag = '';
            }
        }

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
            ->with('contact')
            ->with('tags')
            ->where('wechat_bot_id', $this->wechatBotId)
            ->where('type', WechatContact::TYPES['friend'])
            ->when($this->filters['search'], fn($query, $search) => $query->where('remark', 'like', '%' . $search . '%'));

        return $this->applySorting($query);
    }

    public function getRowsProperty()
    {
        return $this->cache(function () {
            return $this->applyPagination($this->rowsQuery);
        });
    }
}
