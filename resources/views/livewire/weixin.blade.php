<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        状态设置
    </h2>
</x-slot>

<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8  space-y-4">
    <div class="info">
        @if($msg)
            <h2>{!! $msg !!}</h2>
        @endif

        @if($showRemind)
            @if($qr)
                <p>请在1分钟内使用手机微信扫码此二维码</p>
                <img src="{{ $qr }}" alt="">
            @endif
            
            <ol>
                <li>1.拿起手机</li>
                <li>2.打开手机微信</li>
                <li>3.首次绑定需扫码（再次登录手机微信会收到弹窗）</li>
                <li>4.在“iPad微信登录确认页面”点击登录</li>
                <li>5.耐心等待手机微信顶部显示“iPad登录”后 </li>
                <li>6.系统将进入后台（正在载入数据），此过程需要3～5分钟，请等待5分钟后再刷新本页</li>
                <li>7.登录成功后，手机可以断网、飞行模式，都无影响</li>
                <li>8.切勿通过手机退出“iPad登录”，否则将无法再次登录！</li>
            </ol>
        @endif


        @if($who)
            <figure class="md:flex bg-gray-100 rounded-xl p-8 md:p-0">
                <img class="w-36 h-36  mx-auto" src="{{ $who["bigHead"]??$defaultAvatar }}" alt="{{ $who["nickName"] }}" title="{{ $who["nickName"] }}的头像" width="384" height="512">
                <div class="pt-0 md:p-8 space-y-4">
                    <blockquote>
                        <p class="text-lg font-semibold">{{ $who["nickName"] }} ({{ $teamName }})</p>
                    </blockquote>

                    <figcaption class="font-medium">
                        <div class="text-gray-500">
                            {{ isset($who["signature"])?$who["signature"]:'暂无签名' }}
                            <br/>登录时间：{{$loginAt}} 有效期：{{ $expiresAt }} 请使用下面👇的“退出Bot登录”按钮退出！
                        </div>
                        
                        <div class="mt-4">
                            <button wire:click="logout" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray disabled:opacity-25 transition ease-in-out duration-150">退出Bot登录</button>
                        </div>    
                    </figcaption>
                </div>
            </figure>
            <div>

                <x-input.toggle 
                    wire:model="wechatAutoReply" 
                    id="wechatAutoReply" 
                    :checked="$wechatAutoReply" 
                    label="关键词自动回复(AutoReply)"
                    />
                <x-input.toggle 
                    wire:model="wechatAutoReplyRoom" 
                    id="wechatAutoReplyRoom" 
                    :checked="$wechatAutoReplyRoom" 
                    label="关键词自动回复群监听"
                    />
                @if($wechatAutoReply)

                <x-input.toggle 
                    wire:model="wechatTuingReply" 
                    id="wechatTuingReply" 
                    :checked="$wechatTuingReply" 
                    label="图灵机器人自动回复"
                    />
                    @if($wechatTuingReply)
                    <x-input.group for="wechatTulingKey" label="图灵API.Key">
                        <x-jet-input id="wechatTulingKey" type="text" class="mt-1 block w-full"  autocomplete="tulingKey" 
                            wire:model.defer="wechatTulingKey"
                            wire:blur.stop="$set('wechatTulingKey', $event.target.value)"
                            placeholder="88s5a1a8af8b4e6cb071a5033d81bc6c" />
                        <div class="mt-4 text-gray-500 font-small">
                            <a href="http://www.tuling123.com" target="_blank">图灵机器人API文档： http://www.tuling123.com</a>
                        </div>
                    </x-input.group>
                    <x-input.group for="wechatTulingId" label="图灵API.UserId">
                        <x-jet-input id="wechatTulingId" type="text" class="mt-1 block w-full"  autocomplete="tulingId" 
                            wire:model.defer="wechatTulingId"
                            wire:blur.stop="$set('wechatTulingId', $event.target.value)"
                            placeholder="7339791" />
                    </x-input.group>
                    @endif
                @endif

                
                
                <x-input.toggle 
                    wire:model="wechatListenRoom" 
                    id="wechatListenRoom" 
                    :checked="$wechatListenRoom" 
                    label="群消息监听"
                    />
                @if($wechatListenRoom)
                <x-input.toggle 
                    wire:model="wechatListenRoomAll" 
                    id="wechatListenRoomAll" 
                    :checked="$wechatListenRoomAll" 
                    label="监听所有群消息"
                    />
                @endif

                <x-input.toggle 
                    wire:model="wechatListenGh" 
                    id="wechatListenGh" 
                    :checked="$wechatListenGh" 
                    label="公众号消息监听"
                    />
                
                <x-input.toggle 
                    wire:model="wechatWebhook" 
                    id="wechatWebhook" 
                    :checked="$wechatWebhook" 
                    label="开放消息API"
                    />
                @if($wechatWebhook)
                    <x-input.group for="wechatWebhookUrl" label="回调地址">
                        <x-jet-input id="wechatWebhookUrl" type="text" class="mt-1 block w-full"  autocomplete="wechatWebhook" 
                            wire:model.defer="wechatWebhookUrl"
                            wire:blur.stop="$set('wechatWebhookUrl', $event.target.value)"
                            value="{{$wechatWebhookUrl}}"
                            />
                    </x-input.group>
                    <x-input.group for="wechatWebhookSecret" label="回调密钥">
                        <x-jet-input id="wechatWebhookSecret" type="text" class="mt-1 block w-full"  autocomplete="wechatWebhook" 
                            wire:model.defer="wechatWebhookSecret"
                            wire:blur.stop="$set('wechatWebhookSecret', $event.target.value)"
                            value="{{$wechatWebhookSecret}}"
                            />
                    </x-input.group>
                @endif

                <x-input.toggle
                    wire:model="wechatWeiju" 
                    id="wechatWeiju" 
                    :checked="$wechatWeiju" 
                    label="开发者选项"
                    />
                @if($wechatWeiju)
                    <x-input.group for="wechatWeijuWebhook" label="高级回调">
                        <x-jet-input id="wechatWeijuWebhook" type="text" class="mt-1 block w-full"  autocomplete="wechatWebhook" 
                            wire:model.defer="wechatWeijuWebhook"
                            wire:blur.stop="$set('wechatWeijuWebhook', $event.target.value)"
                            value="{{$wechatWeijuWebhook}}"
                            />
                            <div class="mt-4 text-gray-500 font-small">
                                <p class="">更改后，消息将直接从API接收raw conent，而不经过本系统</p>
                                <p>默认：{{route('webhook.weiju')}}</p>
                            </div>
                    </x-input.group>
                @endif
            </div>
            @endif

        @if(auth()->id() ==1) 整体过期时间： {{$allExpiresAt}} @endif
    </div>
</div>