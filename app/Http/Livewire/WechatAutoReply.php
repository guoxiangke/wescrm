<?php


namespace App\Http\Livewire;

use App\Models\WechatBot;
use App\Models\WechatAutoReply as Model;
use Livewire\Component;

use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Models\WechatContent;

class WechatAutoReply extends Component
{
    public $contents;
    public int $wechatBotId;

    public function mount()
    {
        $currentTeamId = auth()->user()->currentTeam->id;
        $this->wechatBotId = WechatBot::where('team_id', $currentTeamId)->pluck('id')->first();
        $this->contents = WechatContent::where('wechat_bot_id', $this->wechatBotId)->pluck('name','id');
        $this->editing = $this->makeBlankModel();

        $this->sorts = ['updated_at'=>'desc']; //默认排序
    }

    public function render()
    {
        return view('livewire.wechat-auto-reply',['models' => $this->rows]);
    }

    use WithPerPagePagination, WithSorting, WithBulkActions,  WithCachedRows;
    public $showEditModal = false;
    public $model = Model::class;
    public $filters = [
        'search' => '',
    ];
    public Model $editing;

    protected $queryString = ['sorts'];

    protected $listeners = ['refreshWechatAutoReplies' => '$refresh'];

    public function rules()
    {
        return [
            'editing.keyword' => 'required|min:3',
            'editing.wechat_content_id' => 'required|integer',
            'editing.wechat_bot_id' => 'required|integer', //default value.
        ];
    }

    public function makeBlankModel()
    {
        return Model::make(['wechat_bot_id' => $this->wechatBotId]);
    }

    public function create()
    {
        $this->useCachedRows();
        if ($this->editing->getKey()) $this->editing = $this->makeBlankModel();
        $this->showEditModal = true;
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

        $this->showEditModal = false;

        $this->dispatchBrowserEvent('notify', 'Saved!');
    }

    public function resetFilters()
    {
        $this->reset('filters');
    }

    public function getRowsQueryProperty()
    {
        $query = Model::query()->with('content')
            ->when($this->filters['search'], fn($query, $search) => $query->where('keyword', 'like', '%' . $search . '%'));

        return $this->applySorting($query);
    }

    public function getRowsProperty()
    {
        return $this->cache(function () {
            return $this->applyPagination($this->rowsQuery);
        });
    }
}