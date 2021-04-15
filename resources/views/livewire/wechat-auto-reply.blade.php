<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        AI自动回复
    </h2>
</x-slot>

<div class="max-w-7xl mx-auto py-10 flex-col space-y-4">
    <!-- Top Bar -->
    <div class="flex justify-between">
        <div class="w-2/4 flex space-x-4 relative">
            <x-input.text wire:model="filters.search" placeholder="Search ..." class="pl-10 pr-5 appearance-none h-10 w-full rounded-full text-sm focus:outline-none" />
            <button type="submit" class="absolute top-0 mt-3 left-0 ml-4">
                <x-icon.search />
            </button>
        </div>

        <div class="space-x-2 flex items-center">
            <x-input.group borderless paddingless for="perPage" label="Per Page">
                <x-input.select wire:model="perPage" id="perPage">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </x-input.select>
            </x-input.group>

            <x-button.primary wire:click="create"><x-icon.plus /> New </x-button.primary>
        </div>
    </div>

    <!-- Table -->
    <x-table>
        <x-slot name="head">
            <x-table.heading class="pr-0 w-8">
                <x-input.checkbox wire:model="selectPage" />
            </x-table.heading>
            <x-table.heading sortable multi-column wire:click="sortBy('name')" :direction="$sorts['name'] ?? null">Keyword</x-table.heading>
            <x-table.heading sortable multi-column class="w-full">Content</x-table.heading>
            <x-table.heading sortable multi-column wire:click="sortBy('created_at')" :direction="$sorts['created_at'] ?? null">Created_at</x-table.heading>
            <x-table.heading sortable multi-column wire:click="sortBy('updated_at')" :direction="$sorts['updated_at'] ?? null">Updated_at</x-table.heading>
            <x-table.heading />
        </x-slot>

        <x-slot name="body">
            @if ($selectPage)
                <x-table.row class="bg-cool-gray-200" wire:key="row-message">
                    <x-table.cell colspan="6">
                        @unless($selectAll)
                            <div>
                                <span>You have selected <strong>{{ $models->count() }}</strong> users, do you want to select
                                    all <strong>{{ $models->total() }}</strong>?</span>
                                <x-button.link wire:click="selectAll" class="ml-1 text-blue-600">Select All</x-button.link>
                            </div>
                        @else
                            <span>You are currently selecting all <strong>{{ $models->total() }}</strong> users.</span>
                @endif
                </x-table.cell>
                </x-table.row>
                @endif

                @forelse ($models as $model)
                    <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $model->id }}">
                        <x-table.cell class="pr-0">
                            <x-input.checkbox wire:model="selected" value="{{ $model->id }}" />
                        </x-table.cell>

                        <x-table.cell>
                            <span href="#" class="inline-flex space-x-2 truncate text-sm leading-5">

                                <p class="text-cool-gray-600 truncate">
                                    {{ $model->keyword }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="text-cool-gray-900 font-medium">{{ $model->content->cnType}} ：{{ $model->content->getContentASText() }} </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium leading-4 bg-{{ $model->status_color }}-100 text-{{ $model->status_color }}-800 capitalize">
                                {{ $model->created_at }}
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium leading-4 bg-{{ $model->status_color }}-100 text-{{ $model->status_color }}-800 capitalize">
                                {{ $model->updated_at }}
                            </span>
                        </x-table.cell>
                        
                        <x-table.cell>
                            <x-button.link wire:click="edit({{ $model->id }})">Edit</x-button.link>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="6">
                            <div class="flex justify-center items-center space-x-2">
                                <x-icon.inbox class="h-8 w-8 text-cool-gray-400" />
                                <span class="font-medium py-8 text-cool-gray-400 text-xl">No items found...</span>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
        </x-slot>
    </x-table>

    <div>
        {{ $models->links() }}
    </div>

    <!-- Save Modal -->
    <form wire:submit.prevent="save">
        <x-modal.dialog :maxWidth="'sm:max-w-lg'" wire:model.defer="showEditModal">
            <x-slot name="title">关键词自动回复</x-slot>

            <x-slot name="content">

                <x-input.group for="name" label="响应的关键词" :error="$errors->first('editing.keyword')">
                    <x-jet-input id="keyword" wire:model="editing.keyword" type="text" class="mt-1 block w-full" autocomplete="keyword" />
                    
                </x-input.group>

                <x-input.group for="name" label="发送内容" :error="$errors->first('editing.wechat_content_id')">
                    <x-input.select id="wechat_content_id" wire:model="editing.wechat_content_id" class="mt-1 block w-full font-medium  text-gray-700" autocomplete="tag" >
                        <option value="">选择已有的发送内容</option>
                        @foreach ($contents as $id => $name)
                        <option value="{{$id}}">{{$id}} . {{$name}}</option>
                        @endforeach
                    </x-input.select>
                    
                    <x-jet-input-error for="name" class="mt-2" />
                </x-input.group>
                <div class="mt-3 block space-y-2 text-md font-medium leading-5 text-gray-700">
                    <div class="space-y-1">
                        <p>关键词格式：</p>
                        <p>&nbsp;&nbsp;hi*  代表以hi开头的</p>
                        <p>*hi  &nbsp;&nbsp;代表以hi结尾的</p>
                        <p>*hi* 代表包含hi的</p>
                        <p>*hi*thanks*  代表先hi后thanks的</p>
                    </div>

                    <p>按更新顺序，第一个匹配的关键词，优先响应</p>
                    <p><a href="{{ route('channel.wechat.content') }}">如无您想要的内容，请先创建</a></p>
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="$set('showEditModal', false)">Cancel</x-button.secondary>
                <x-button.primary type="submit">Save</x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>

</div>