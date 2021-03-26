<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        素材库
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

            <x-button.primary wire:click="$set('showBatchModal', true)">群发助手 </x-button.primary>
            <x-button.primary wire:click="create"><x-icon.plus /> New </x-button.primary>
        </div>
    </div>

    <!-- Table -->
    <x-table>
        <x-slot name="head">
            <x-table.heading class="pr-0 w-8">
                <x-input.checkbox wire:model="selectPage" />
            </x-table.heading>
            <x-table.heading multi-column>Title</x-table.heading>
            <x-table.heading multi-column
            class="w-full">Content</x-table.heading>
            <x-table.heading sortable multi-column wire:click="sortBy('created_at')" :direction="$sorts['created_at'] ?? null">
                Created_at</x-table.heading>
            <x-table.heading sortable multi-column wire:click="sortBy('updated_at')" :direction="$sorts['updated_at'] ?? null">
                Updated_at</x-table.heading>
            <x-table.heading />
        </x-slot>

        <x-slot name="body">
            @if ($selectPage)
                <x-table.row class="bg-cool-gray-200" wire:key="row-message">
                    <x-table.cell colspan="6">
                        @unless($selectAll)
                            <div>
                                <span>You have selected <strong>{{ $models->count() }}</strong> items, do you want to select
                                    all <strong>{{ $models->total() }}</strong>?</span>
                                <x-button.link wire:click="selectAll" class="ml-1 text-blue-600">Select All</x-button.link>
                            </div>
                        @else
                            <span>You are currently selecting all <strong>{{ $models->total() }}</strong> items.</span>
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
                                    {{ $model->name }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="text-cool-gray-900 font-medium">{{ $model->cnType}} ：{{ $model->getContentASText() }} </span>
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
            <x-slot name="title">创建内容</x-slot>

            <x-slot name="content">

                <x-input.group for="name" label="内容描述" :error="$errors->first('editing.name')">
                    <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="editing.name" autocomplete="name" />
                </x-input.group>

                <x-input.group for="type" label="内容类型" :error="$errors->first('editing.type')">
                    <x-input.select id="type" class="mt-1 block w-full font-medium  text-gray-700" wire:model.defer="editing.type" autocomplete="type" >
                        <option value="">请选择内容类型</option>
                        @foreach ($editing::TYPES_CN as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                        @endforeach
                        
                    </x-input.select>
                </x-input.group>

                <x-input.group for="content" label="内容" :error="$errors->first('editing.content')">
                    <x-input.textarea id="content" type="time" class="mt-1 block w-full" wire:model.defer="editing.content" autocomplete="content"   placeholder="json"/>
                </x-input.group>
                <div class="mt-3 block text-sm font-medium leading-5 text-gray-700">
                    <ul>
                    <li>文本： {"content": "主动发送 文本/链接/名片/图片/视频 消息到好友/群"}</li> 
                    <li>图片： {"content": "https://www.upyun.com/static/logos/dangdang.png"}</li> 
                    <li>链接： {"url": "https://weibo.com", "title": "测试链接到百度", "thumbUrl": "https://www.upyun.com/static/logos/dangdang.png", "description": "其实我放的是微博的链接"}</li> 
                    <li>名片： {"nickName": "nothing", "nameCardId": "bluesky_still"}</li>
                    <li>视频： {"path": "https://abc.yilindeli.com/teach/LevelTestMaterial/0zhumuTestFiles/test.mp4", "thumbPath": "https://www.upyun.com/static/logos/dangdang.png"}</li> 
                    </ul>
                </div>  
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="$set('showEditModal', false)">Cancel</x-button.secondary>

                <x-button.primary type="submit">Save</x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>




    <!-- Delete Modal -->
    <form wire:submit.prevent="sendByTags">
        <x-modal.dialog wire:model.defer="showBatchModal">
            <x-slot name="title">创建群发</x-slot>

            <x-slot name="content">
                <x-input.group for="name" label="选择分组">
                    <x-input.select multiple id="tag" class="mt-1 block w-full font-medium  text-gray-700" 
                        wire:model.lazy="selectedTags">
                        @foreach ($tags as $tag)
                            <option>{{$tag}}</option>
                        @endforeach
                    </x-input.select>
                </x-input.group>

                <x-input.group for="send_at" label="发送定时">
                    <x-jet-input id="send_at" type="time" class="mt-1 block w-full" wire:model.lazy="sendAt" autocomplete="send_at" />
                </x-input.group>

                <x-input.group for="contentId" label="发送内容">
                    <x-input.select id="contentId" wire:model.defer="contentId" class="mt-1 block w-full font-medium  text-gray-700" autocomplete="tag" >
                        <option value="">请选择发送内容</option>
                        @foreach ($contents as $id => $name)
                        <option value="{{$id}}">{{$id}} . {{$name}}</option>
                        @endforeach
                    </x-input.select>
                    
                    <x-jet-input-error for="contentId" class="mt-2" />
                </x-input.group>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="$set('showBatchModal', false)">Cancel</x-button.secondary>
                <x-button.primary type="submit">发送</x-button.primary>
                <x-button.primary wire:click="testSend">测试发送</x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>
</div>