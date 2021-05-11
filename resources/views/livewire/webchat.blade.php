<div id="root" wire:poll.30000ms>
  <div id="mobile-channel-list" 
    class="{{$isMobileShowContact?'show':''}}">
    <div class="str-chat str-chat-channel-list messaging {{ $isDarkUi?"dark":"light"  }}">
      <div class="l-header p-4 mb-1">
        <div class="flex justify-between p-4">
          <div class="flex">
            <div data-testid="avatar" class="str-chat__avatar str-chat__avatar--circle" title="holy-dew-9" style="width: 40px; height: 40px; flex-basis: 40px; line-height: 40px; font-size: 20px;">
              <img data-testid="avatar-img" src="{{$user->profile_photo_url}}" alt="{{$user->name}}" class="str-chat__avatar-image str-chat__avatar-image--loaded" style="width: 40px; height: 40px; flex-basis: 40px; object-fit: cover;">
            </div>
            <div>
              <div class="messaging__channel-list__header__name">{{$user->name}}</div>
              <div class="messaging__channel-list__header__name-2">{{$user->currentTeam->name}}</div>
            </div>
          </div>

          <div class="flex">
            <button title="切换主题" wire:click="$toggle('isDarkUi')" class="messaging__channel-list__header__button">
              <svg width="18" height="18" viewBox="0 0 35 35" xmlns="http://www.w3.org/2000/svg"><path d="M18.44,34.68a18.22,18.22,0,0,1-2.94-.24,18.18,18.18,0,0,1-15-20.86A18.06,18.06,0,0,1,9.59.63,2.42,2.42,0,0,1,12.2.79a2.39,2.39,0,0,1,1,2.41L11.9,3.1l1.23.22A15.66,15.66,0,0,0,23.34,21h0a15.82,15.82,0,0,0,8.47.53A2.44,2.44,0,0,1,34.47,25,18.18,18.18,0,0,1,18.44,34.68ZM10.67,2.89a15.67,15.67,0,0,0-5,22.77A15.66,15.66,0,0,0,32.18,24a18.49,18.49,0,0,1-9.65-.64A18.18,18.18,0,0,1,10.67,2.89Z"/></svg>
            </button>

            @if(!$isCreating)
            <button title="逐个群发/拉群发送？" wire:click="$toggle('isCreating')" class="messaging__channel-list__header__button">
              <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M18 17.9708H0V13.7278L13.435 0.292787C13.8255 -0.0975955 14.4585 -0.0975955 14.849 0.292787L17.678 3.12179C18.0684 3.51229 18.0684 4.14529 17.678 4.53579L6.243 15.9708H18V17.9708ZM2 15.9708H3.414L12.728 6.65679L11.314 5.24279L2 14.5568V15.9708ZM15.556 3.82879L14.142 5.24279L12.728 3.82879L14.142 2.41479L15.556 3.82879Z" fill="#E9E9EA">
                </path>
              </svg>
            </button>
            @endif

            <div class="close-mobile-create"
              wire:click="$toggle('isMobileShowContact')">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2Z" fill="#858688">
                </path>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M16.7247 15.3997L13.325 12L16.7247 8.60029C17.0918 8.23324 17.0918 7.64233 16.7247 7.27528C16.3577 6.90824 15.7668 6.90824 15.3997 7.27528L12 10.675L8.60029 7.27528C8.23324 6.90824 7.64233 6.90824 7.27528 7.27528C6.90824 7.64233 6.90824 8.23324 7.27528 8.60029L10.675 12L7.27528 15.3997C6.90824 15.7668 6.90824 16.3577 7.27528 16.7247C7.64233 17.0918 8.23324 17.0918 8.60029 16.7247L12 13.325L15.3997 16.7247C15.7668 17.0918 16.3577 17.0918 16.7247 16.7247C17.0892 16.3577 17.0892 15.7642 16.7247 15.3997Z" fill="white">
                </path>
              </svg>
            </div>
          </div>
        </div>
        

        <div class="messaging-create-channel-0 relative">
          <header>
            <div class="messaging-create-channel__left-0">
              <div class="users-input-container relative">
                  <input placeholder="Start typing for suggestions" type="text" class="messaging-create-channel__input
                  border-gray-300 border-indigo-200 ring ring-indigo-100 ring-opacity-40 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full" value=""
                    wire:model.debounce.1000ms="search" >
                    <div class="absolute top-0 mt-3 right-4 ml-4">
                      <x-icon.search />
                    </div>
              </div>
            </div>
          </header>
          <main>
            <ul class="messaging-create-channel__user-results absolute top-9 mt-3 left-10 ml-4 w-full">
              <div>
                @if($search)
                  @forelse ($searchIds as $contactId=>$remark)
                    @php
                        $defaultAvatar = "https://ui-avatars.com/api/?name={$remark}&color=7F9CF5&background=EBF4FF";
                    @endphp
                    <div class="messaging-create-channel__user-result"
                      wire:click="$set('currentConversationId', {{$contactId}})">
                      <li class="messaging-create-channel__user-result">
                        <div data-testid="avatar" class="str-chat__avatar str-chat__avatar--circle" style="width: 40px; height: 40px; flex-basis: 40px; line-height: 40px; font-size: 20px;">
                          <img data-testid="avatar-imgs" src="{{ $contactsArray[$contactId]['smallHead']??$defaultAvatar }}" style="width: 40px; height: 40px; flex-basis: 40px; object-fit: cover;">
                        </div>
                        <div class="messaging-create-channel__user-result__details">
                          <span>{{$remark}}</span>
                        </div>
                      </li>
                    </div>
                  @empty
                    <div class="messaging-create-channel__user-result empty">
                        <p>No wechatBotContacts</p>
                    </div>
                  @endforelse
                @endif
              </div>
            </ul>
          </main>
        </div>
      </div>
      <div class="messaging__channel-list">
        @foreach ($lastMessageContacts as $cid => $conversation)
        @php
            $contactId = $conversation['conversation'];
            // $conversation = end($conversation); //最后一个对方发的消息
            $time = $conversation['updated_at']??now();
            $updatedAt = Illuminate\Support\Carbon::parse($time)->setTimezone('Asia/Shanghai')->toDateTimeString();
            $name = $remarks[$contactId]??'G'.$contactsArray[$contactId]['id'];
            // $name = $contactsArray[$contactId]['nickName']?:'G'.$contactsArray[$contactId]['id'];
            $fallbackAvatar = "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF";
            
            if(is_array($conversation['content'])) $content = $conversation['content']['content']??'新消息🆕';
            if(is_array($content)) $content = "未处理消息";
        @endphp
        <div wire:click="$set('currentConversationId', {{$contactId}})" data-id="{{$cid}}" class="channel-preview__container {{ $currentConversationId===$contactId?'selected':'' }}">
          <div class="channel-preview__avatars">
            <img data-testid="avatar-img" src="{{$contactsArray[$contactId]['smallHead']?:$fallbackAvatar}}" alt="" class="str-chat__avatar-image str-chat__avatar-image--loaded">
          </div>
          <div class="channel-preview__content-wrapper">
            <div class="channel-preview__content-top">
              <p class="channel-preview__content-name">{{ $name }}</p>
              <p class="channel-preview__content-time">{{ $updatedAt }}</p>
            </div>
            <p class="channel-preview__content-message">{{ $content }} </p>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  
  <div class="str-chat str-chat-channel messaging {{ $isDarkUi?"dark":"light"  }}">
    <div class="str-chat__container">
      @if($isCreating)
        <div class="messaging-create-channel">
          <header>
            <div class="messaging-create-channel__left">
              <div class="messaging-create-channel__left-text">To:</div>
              <div class="users-input-container">
                <div class="hidden messaging-create-channel__users">
                  <div class="messaging-create-channel__user">
                    <div class="messaging-create-channel__user-text">aged-salad-0</div>
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M9.72472 8.39971L6.325 5L9.72472 1.60029C10.0918 1.23324 10.0918 0.642327 9.72472 0.275283C9.35767 -0.091761 8.76676 -0.091761 8.39971 0.275283L5 3.675L1.60029 0.275283C1.23324 -0.091761 0.642327 -0.091761 0.275283 0.275283C-0.091761 0.642327 -0.091761 1.23324 0.275283 1.60029L3.675 5L0.275283 8.39971C-0.091761 8.76676 -0.091761 9.35767 0.275283 9.72472C0.642327 10.0918 1.23324 10.0918 1.60029 9.72472L5 6.325L8.39971 9.72472C8.76676 10.0918 9.35767 10.0918 9.72472 9.72472C10.0892 9.35767 10.0892 8.76415 9.72472 8.39971Z" fill="white">
                      </path>
                    </svg>
                  </div>
                </div>
                <form>
                  <input placeholder="Start typing for suggestions" type="text" class="messaging-create-channel__input
                  border-gray-300 border-indigo-200 ring ring-indigo-100 ring-opacity-40 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full" value=""
                    wire:model.debounce.1000ms="search" >
                </form>
              </div>
              <div class="close-mobile-create"
                wire:click="$toggle('isMobileShowContact')">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2Z" fill="#858688">
                  </path>
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M16.7247 15.3997L13.325 12L16.7247 8.60029C17.0918 8.23324 17.0918 7.64233 16.7247 7.27528C16.3577 6.90824 15.7668 6.90824 15.3997 7.27528L12 10.675L8.60029 7.27528C8.23324 6.90824 7.64233 6.90824 7.27528 7.27528C6.90824 7.64233 6.90824 8.23324 7.27528 8.60029L10.675 12L7.27528 15.3997C6.90824 15.7668 6.90824 16.3577 7.27528 16.7247C7.64233 17.0918 8.23324 17.0918 8.60029 16.7247L12 13.325L15.3997 16.7247C15.7668 17.0918 16.3577 17.0918 16.7247 16.7247C17.0892 16.3577 17.0892 15.7642 16.7247 15.3997Z" fill="white">
                  </path>
                </svg>
              </div>
            </div>
            <button class="create-channel-button">Start chat</button>
          </header>
          <main>
            <ul class="messaging-create-channel__user-results">
            </ul>
          </main>
        </div>
      @else
        <div class="str-chat__main-panel">
          <div class="messaging__channel-header">
            <div id="mobile-nav-icon" 
              wire:click="$toggle('isMobileShowContact')"
              class="{{ $isDarkUi?"dark":"light" }}">
              <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg" style="cursor: pointer; margin: 10px;">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.5 0.333344H15.5V2.00001H0.5V0.333344ZM0.5 6.16667H15.5V7.83334H0.5V6.16667ZM15.5 12H0.5V13.6667H15.5V12Z" fill="white">
                </path>
              </svg>
            </div>
            <div class="messaging__channel-header__avatars"> 
              <img data-testid="avatar-img" src="{{ $contactsArray[$currentConversationId]['smallHead']??$defaultAvatar }}" alt="" class="str-chat__avatar-image str-chat__avatar-image--loaded">
            </div>
            <div class="channel-header__name">{{ $remarks[$currentConversationId]??'暂无群名'.$contactsArray[$currentConversationId]['id'] }}</div>
            <div class="messaging__channel-header__right">
              <div class="messaging__typing-indicator">
                <div>
                </div>
              </div>
            </div>
          </div>
          <div class="str-chat__list" x-ref="foo">
            <div class="str-chat__reverse-infinite-scroll" data-testid="reverse-infinite-scroll">
              <ul class="str-chat__ul">
                @foreach ($conversations[$currentConversationId] as $message)
                  @php
                      $time = $message['updated_at']??now();
                      $updatedAt = Illuminate\Support\Carbon::parse($time)->setTimezone('Asia/Shanghai')->toDateTimeString();
                  @endphp
                  @if ($loop->first)
                    <li class="">
                      <div class="str-chat__date-separator">
                        <hr class="str-chat__date-separator-line">
                        <div class="str-chat__date-separator-date">{{ $updatedAt }}</div>
                      </div>
                    </li>
                    @if( !isset($LoadMore[$currentConversationId]) 
                      && $conversationFirstId && !isset($hideLoadMore[$currentConversationId]))
                    <div class=" str-chat__list-notifications">
                      <button 
                      wire:click="loadMore"
                      data-testid="message-notification" class="str-chat__message-notification">Load More!</button>
                    </div>
                    @endif
                  @endif
                
                @php
                  // 群成员名字/本系统发送：座席名字/主动发送：bot自己的名字
                  $isBot = $message['from_contact_id']?false:true;
                  if($isBot){ 
                    //分2种情况
                    // 1.从手机发送 显示bot头像
                    // 2.从webchat发送，显示座席头像
                    $name = $message['seat_user_id']
                        ? $seatUsers[$message['seat_user_id']]['name']
                        : $wechatBot->nickName;
                    
                    $avatar = $message['seat_user_id']
                        ? $seatUsers[$message['seat_user_id']]['profile_photo_url']
                        : ($wechatBot->bigHead?:$defaultAvatar);
                  }else{
                    $name = $contactsArray[$message['from_contact_id']]['nickName']?$contactsArray[$message['from_contact_id']]['nickName']:($message['from_contact_id']%100);
                    $fallbackAvatar = "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF";
                    $avatar =  $contactsArray[$message['from_contact_id']]['smallHead']?:$fallbackAvatar;
                  }
                @endphp

                <li class="pb-4" data-id="conversation-{{$message['id']??'0'}}">
                  <div 
                    class="str-chat__message str-chat__message-simple str-chat__message--regular str-chat__message--received str-chat__message--has-text 
                    {{ $isBot?'str-chat__message--me str-chat__message-simple--me':'' }} 
                    ">
                      <span class=" str-chat__message-simple-status" data-testid="message-status-received">
                        <div class="str-chat__tooltip">Delivered</div>
                        <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm3.72 6.633a.955.955 0 1 0-1.352-1.352L6.986 8.663 5.633 7.31A.956.956 0 1 0 4.28 8.663l2.029 2.028a.956.956 0 0 0 1.353 0l4.058-4.058z" fill="#006CFF" fill-rule="evenodd"></path>
                        </svg>
                      </span>

                      <div class="str-chat__avatar str-chat__avatar--circle" title="solitary-shadow-5" style="display:block ;width: 32px; height: 32px; flex-basis: 32px; line-height: 32px; font-size: 16px;">
                        <img data-testid="avatar-img" 
                          src="{{ $avatar }}"
                          alt="{{ $name }}"
                          class="str-chat__avatar-image str-chat__avatar-image--loaded" 
                          style="width: 32px; height: 32px; flex-basis: 32px; object-fit: cover;">
                      </div>

                    @php
                      $content = $message['content']['content']??"暂未处理的{$message['msgType']}消息";
                      if(is_array($content)){//{"content":[]}
                        $content = "消息处理失败，内容为空";
                      }
                      // info($message);
                      // info($content,[$message['id']]);
                      // TODO   处理 content   
                      $now = now();
                      $time = $message['updated_at']??$now;
                      $updatedAt = Illuminate\Support\Carbon::parse($time);
                      $time = $updatedAt->setTimezone('Asia/Shanghai')->toDateTimeString();
                    @endphp
                    <div data-testid="message-inner" class="str-chat__message-inner">
                      <div class="str-chat__message-text">
                        @switch($message['msgType'])
                            @case(3)
                                <div class="str-chat__message-attachment str-chat__message-attachment--image str-chat__message-attachment--image str-chat__message-attachment--image--">
                                  <img class="str-chat__message-attachment--img" src="{{ $content }}" data-testid="image-test">
                                </div>
                                @break
                            @case(42)
                                <div class="str-chat__message-attachment str-chat__message-attachment--image str-chat__message-attachment--image str-chat__message-attachment--image--">
                                  <p>名片推荐：{{ $content }}  {{ $message['content']['alias']??''  }}</p>
                                  <img style="max-height: 100px;" src="{{ $message['content']['smallheadimgurl']??$defaultAvatar }}" data-testid="image-test">
                                </div>
                                @break
                            @case(48)
                                <div data-testid="message-text-inner-wrapper" class="str-chat__message-text-inner str-chat__message-simple-text-inner">
                                  <p>收到位置消息：{{ $content }}<br/>请到手机微信查看</p>
                                </div>
                                @break
                            @case(47)
                                <div class="str-chat__message-attachment str-chat__message-attachment--image str-chat__message-attachment--image str-chat__message-attachment--image--">
                                  <img style="max-height: 100px;" src="{{ $content }}" data-testid="image-test">
                                </div>
                                @break
                            @case(34)
                                <audio class='audio' preload="none" controls src='{{ $content }}' controlslist="nodownload" />
                                @break
                            @case(301)
                              <div>
                                <p>收到音频消息：{{ $content }}</p>
                                <audio class='audio' preload="none" controls src='{{ $message['content']['url'] }}' controlslist="nodownload" />
                              </div>
                                @break
                            @case(43)
                                <video class='video' preload="none" controls src='{{ $content }}' controlslist="nodownload" />
                                @break
                            @case(331)
                                <div data-testid="message-text-inner-wrapper" class="str-chat__message-text-inner str-chat__message-simple-text-inner">
                                  <p>收到[{{$message['content']['sourcedisplayname']}}]小程序消息：<br/>{{ $content }}<br/>请到手机微信查看</p>
                                </div>
                                @break
                            @case(496)
                                @if(isset($message['content']['fileext']) && $message['content']['fileext']=='mp3')
                                  <audio class='audio' controls src='{{$content}}?ext=.mp3' controlslist="nodownload" />
                                  @break
                                @endif

                                @if($content!="暂未处理的49消息")
                                <div class="str-chat__message-attachment str-chat__message-attachment--file str-chat__message-attachment--file str-chat__message-attachment--file--">
                                  <div data-testid="attachment-file" class="str-chat__message-attachment-file--item">
                                    @isset($message['content']['fileext'])
                                    <svg viewBox="0 0 48 48" width="30" height="30" style="max-width: 100%;"><defs><clipPath id="pageRadius2"><rect x="4" y="0" rx="4" ry="4" width="40" height="48"></rect></clipPath><clipPath id="foldCrop"><rect width="40" height="12" transform="rotate(-45 0 12)"></rect></clipPath><linearGradient x1="100%" y1="0%" y2="100%" id="pageGradient2"><stop stop-color="white" stop-opacity="0.25" offset="0%"></stop><stop stop-color="white" stop-opacity="0" offset="66.67%"></stop></linearGradient></defs><g clip-path="url(#pageRadius2)"><path d="M4 0 h 28 L 44 12 v 36 H 4 Z" fill="whitesmoke"></path><path d="M4 0 h 28 L 44 12 v 36 H 4 Z" fill="url(#pageGradient2)"></path></g><g transform="translate(32 12) rotate(-90)"><rect width="40" height="48" fill="#dbdbdb" rx="4" ry="4" clip-path="url(#foldCrop)"></rect></g><g id="label"><rect fill="#a8a8a8" x="4" y="34" width="40" height="14" clip-path="url(#pageRadius2)"></rect></g><g id="labelText" transform="translate(4 34)">
                                      <text x="20" y="10" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif" font-size="9" fill="white" text-anchor="middle" style="font-weight: bold; text-align: center; pointer-events: none; text-transform: none; user-select: none;">
                                        {{$message['content']['fileext']}}
                                      </text></g>
                                    </svg>
                                    @endisset
                                    <div class="str-chat__message-attachment-file--item-text">
                                      <a href="{{$content}}?ext={{$message['content']['fileext']??'unknown'}}" target="_blank">{{$message['content']['title']??'unknown'}}</a>
                                      @isset($message['content']['totallen'])
                                      <span>{{$message['content']['totallen']/1000}} kB</span>
                                      @endisset
                                    </div>
                                  </div>
                                </div>
                                @break
                                @endif

                                <div data-testid="message-text-inner-wrapper" class="str-chat__message-text-inner str-chat__message-simple-text-inner">
                                  <p>{{ $content }}</p>
                                </div>
                                @break
                            @default
                              <div data-testid="message-text-inner-wrapper" class="str-chat__message-text-inner str-chat__message-simple-text-inner">
                                <p>{{ $content }}</p>
                              </div>
                        @endswitch
                      </div>
                      
                      <div class="str-chat__message-data str-chat__message-simple-data">
                        <span class="str-chat__message-simple-name">{{ $name }}</span>
                        <time class="str-chat__message-simple-timestamp" datetime="" title="">{{ $time }}</time>
                      </div>
                      @if($message['msgType']=='491' && !$isBot)
                      <div class="absolute" style="
                      font-size: 10px;
                      background-color: black;
                      color:white;
                      padding: .5em;
                      text-align: left;">
                        <span style="font-size: 14px;">❝</span>
                        <span>{{$message['content']['refermsg']['displayname']??''}}:</span>
                        {{$message['content']['refermsg']['content']??'暂未处理内容'}}
                      </div>
                    @endif
                      
                    </div>
                  </div>

                </li>
                @endforeach
              
              </ul>
              <div>
              </div>
            </div>
          </div>
          
          <div class="str-chat__messaging-input">
            <div  style="position: absolute; top: -30%; left: 45%; transform: translate(-50%, -50%); z-index: 999;">
              <div wire:loading wire:target="file">文件上传中...</div>
              @if($file)
              <p>
                {{$attach}}
                <span wire:click="resetFile">
                  <svg style="display:inline; cursor: pointer; margin-right: 10px;" width="20" height="20" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 20C0 8.95431 8.95431 0 20 0C31.0457 0 40 8.95431 40 20C40 31.0457 31.0457 40 20 40C8.95431 40 0 31.0457 0 20Z" fill="#005fff"></path>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M27.5625 25.4416L22.1208 20L27.5625 14.5583C28.15 13.9708 28.15 13.025 27.5625 12.4375C26.975 11.85 26.0291 11.85 25.4416 12.4375L20 17.8791L14.5583 12.4375C13.9708 11.85 13.025 11.85 12.4375 12.4375C11.85 13.025 11.85 13.9708 12.4375 14.5583L17.8791 20L12.4375 25.4416C11.85 26.0291 11.85 26.975 12.4375 27.5625C13.025 28.15 13.9708 28.15 14.5583 27.5625L20 22.1208L25.4416 27.5625C26.0291 28.15 26.975 28.15 27.5625 27.5625C28.1458 26.975 28.1458 26.025 27.5625 25.4416Z" fill="#E9E9EA"></path>
                  </svg>
                </span>
              </p>
              @endif
            </div>
              <div class="messaging-input__button emoji-button {{ $isEmojiPicker?'active':'' }}" role="button" aria-roledescription="button"
                wire:click="$toggle('isEmojiPicker')">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <g><path
                              fill-rule="evenodd"
                              clip-rule="evenodd"
                              d="M10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2ZM0 10C0 4.47715 4.47715 0 10 0C15.5228 0 20 4.47715 20 10C20 15.5228 15.5228 20 10 20C4.47715 20 0 15.5228 0 10Z"
                          ></path>
                          <path fill-rule="evenodd" clip-rule="evenodd" d="M9 7.5C9 8.32843 8.32843 9 7.5 9C6.67157 9 6 8.32843 6 7.5C6 6.67157 6.67157 6 7.5 6C8.32843 6 9 6.67157 9 7.5Z"></path>
                          <path fill-rule="evenodd" clip-rule="evenodd" d="M14 7.5C14 8.32843 13.3284 9 12.5 9C11.6716 9 11 8.32843 11 7.5C11 6.67157 11.6716 6 12.5 6C13.3284 6 14 6.67157 14 7.5Z"></path>
                          <path
                              fill-rule="evenodd"
                              clip-rule="evenodd"
                              d="M5.42662 11.1808C5.87907 10.8641 6.5026 10.9741 6.81932 11.4266C7.30834 12.1252 8.21252 12.9219 9.29096 13.1459C10.275 13.3503 11.6411 13.1262 13.2568 11.3311C13.6263 10.9206 14.2585 10.8873 14.6691 11.2567C15.0796 11.6262 15.1128 12.2585 14.7434 12.669C12.759 14.8738 10.7085 15.4831 8.88421 15.1041C7.15432 14.7448 5.8585 13.5415 5.18085 12.5735C4.86414 12.121 4.97417 11.4975 5.42662 11.1808Z"
                          ></path>
                      </g>
                  </svg>
              </div>
              <div tabindex="0" class="rfu-dropzone rfu-dropzone---accept" style="position: relative;">
                  <div class="messaging-input__input-wrapper">
                      <div class="rta str-chat__textarea">
                        <textarea
                          x-on:change="console.log($event.target.value.trim().length)"
                          id="input-textarea"
                          wire:model.debounce.3000ms="content" 
                          wire:keydown.enter.prevent="send"
                          wire:click.prevent="$set('isEmojiPicker', false)"
                          @if($file) 
                          placeholder="点击最右侧按钮发送文件"
                          @else
                          placeholder="在此输入文本消息"
                          @endif
                          rows="1" 
                          class="rta__textarea str-chat__textarea__textarea" spellcheck="false" style="height: 38px !important;"></textarea>
                      </div>
                  </div>
              </div>
              
              <input name="file" type="file" id="file"
                  accept=".jpg,.png,.gif,.mp4,.pdf,.doc,.docx,.txt,.md,.pptx,.ppt,.zip"
                  wire:model="file" class="hidden">
              <label for="file" class="messaging-input__button @if($file) active @endif" role="button" aria-roledescription="button">
                <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 512 512" width="20"><path d="m256 512c-141.164062 0-256-114.835938-256-256s114.835938-256 256-256 256 114.835938 256 256-114.835938 256-256 256zm0-480c-123.519531 0-224 100.480469-224 224s100.480469 224 224 224 224-100.480469 224-224-100.480469-224-224-224zm0 0"/><path d="m368 272h-224c-8.832031 0-16-7.167969-16-16s7.167969-16 16-16h224c8.832031 0 16 7.167969 16 16s-7.167969 16-16 16zm0 0"/><path d="m256 384c-8.832031 0-16-7.167969-16-16v-224c0-8.832031 7.167969-16 16-16s16 7.167969 16 16v224c0 8.832031-7.167969 16-16 16zm0 0"/></svg> 
              </label>

              <div wire:click="send" class="messaging-input__button @if($file) active @endif" role="button" aria-roledescription="button">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M20 10C20 4.48 15.52 0 10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10ZM6 9H10V6L14 10L10 14V11H6V9Z" fill="white"></path>
                  </svg>
              </div>
              
                <div class="str-chat__input--emojipicker {{$isEmojiPicker?'':'hidden'}}">
                  <section class="emoji-mart emoji-mart-light" aria-label="Pick your emoji" style="width: 338px;">
                    
                      <div class="emoji-mart-scroll">
                          <section class="emoji-mart-category" aria-label="Smileys &amp; People">
                            <ul class="emoji-mart-category-list" id="wxemoji" style="">
                              @foreach ($codes as $id=>$code)
                              <div class="weui-emotion_item" data-idx="{{$id}}" data-code="{{$code}}" 
                                wire:click="insertEmoji('{{$code}}')" >
                                <div class="bg-icons weui-icon_emotion {{$styles[$id]}}"></div>
                              </div>
                              @endforeach
                            </ul>
                         </section>
                         <section class="emoji-mart-category" aria-label="Frequently Used">
                             <div data-name="Recent" class="emoji-mart-category-label"><span aria-hidden="true">Frequently Used</span></div>
                             <ul class="emoji-mart-category-list">
                               @foreach ($emojis as $emoji)
                                 <li>
                                   <button 
                                    wire:click="insertEmoji('{{$emoji}}')"
                                    class="emoji-mart-emoji emoji-mart-emoji-native" type="button">
                                       <span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">{{$emoji}}</span>
                                   </button>
                                 </li>
                               @endforeach
                             </ul>
                         </section>
                      </div>
                  </section>
                </div>            
              
          </div>
        </div>
      @endif

        <div class="str-chat__thread" 
          style="display: {{$isThread?'':'none'}};">
          <div class="custom-thread-header">
              <div class="custom-thread-header__left">
                  <p class="custom-thread-header__left-title">CRM</p>
                  <p class="custom-thread-header__left-count">todo Thread</p>
              </div>
              <div wire:click="$toggle('isThread')" >
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" style="cursor: pointer; margin-right: 10px;">
                  <path d="M0 20C0 8.95431 8.95431 0 20 0C31.0457 0 40 8.95431 40 20C40 31.0457 31.0457 40 20 40C8.95431 40 0 31.0457 0 20Z" fill="#3E3E41"></path>
                  <path
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M27.5625 25.4416L22.1208 20L27.5625 14.5583C28.15 13.9708 28.15 13.025 27.5625 12.4375C26.975 11.85 26.0291 11.85 25.4416 12.4375L20 17.8791L14.5583 12.4375C13.9708 11.85 13.025 11.85 12.4375 12.4375C11.85 13.025 11.85 13.9708 12.4375 14.5583L17.8791 20L12.4375 25.4416C11.85 26.0291 11.85 26.975 12.4375 27.5625C13.025 28.15 13.9708 28.15 14.5583 27.5625L20 22.1208L25.4416 27.5625C26.0291 28.15 26.975 28.15 27.5625 27.5625C28.1458 26.975 28.1458 26.025 27.5625 25.4416Z"
                      fill="#E9E9EA"
                  ></path>
              </svg>
              </div>
          </div>
          <div class="str-chat__thread-list">
              <div class="str-chat__message str-chat__message-simple str-chat__message--regular str-chat__message--received str-chat__message--has-text">
                  <div data-testid="avatar" class="str-chat__avatar str-chat__avatar--circle" title="plain-paper-2" style="width: 32px; height: 32px; flex-basis: 32px; line-height: 32px; font-size: 16px;">
                      <img
                          data-testid="avatar-img"
                          src="{{ $defaultAvatar }}"
                          alt="p"
                          class="str-chat__avatar-image str-chat__avatar-image--loaded"
                          style="width: 32px; height: 32px; flex-basis: 32px; object-fit: cover;"
                      />
                  </div>
                  <div data-testid="message-inner" class="str-chat__message-inner">
                      <div class="str-chat__message-text">
                          <div data-testid="message-text-inner-wrapper" class="str-chat__message-text-inner str-chat__message-simple-text-inner"><p>hi</p></div>
                      </div>
                      <div class="str-chat__message-data str-chat__message-simple-data">
                          <span class="str-chat__message-simple-name">plain-paper-2</span>
                          <time class="str-chat__message-simple-timestamp" datetime="" title="">Today at 3:41 PM</time>
                      </div>
                  </div>
              </div>
              <div class="str-chat__thread-start">Start of a new thread</div>
              <div class="str-chat__list str-chat__list--thread"></div>
              <div class="str-chat__list-notifications"></div>
          </div>
          <div class="str-chat__messaging-input">
              <div class="messaging-input__button emoji-button" role="button" aria-roledescription="button">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <g>
                          <path
                              fill-rule="evenodd"
                              clip-rule="evenodd"
                              d="M10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2ZM0 10C0 4.47715 4.47715 0 10 0C15.5228 0 20 4.47715 20 10C20 15.5228 15.5228 20 10 20C4.47715 20 0 15.5228 0 10Z"
                          ></path>
                          <path fill-rule="evenodd" clip-rule="evenodd" d="M9 7.5C9 8.32843 8.32843 9 7.5 9C6.67157 9 6 8.32843 6 7.5C6 6.67157 6.67157 6 7.5 6C8.32843 6 9 6.67157 9 7.5Z"></path>
                          <path fill-rule="evenodd" clip-rule="evenodd" d="M14 7.5C14 8.32843 13.3284 9 12.5 9C11.6716 9 11 8.32843 11 7.5C11 6.67157 11.6716 6 12.5 6C13.3284 6 14 6.67157 14 7.5Z"></path>
                          <path
                              fill-rule="evenodd"
                              clip-rule="evenodd"
                              d="M5.42662 11.1808C5.87907 10.8641 6.5026 10.9741 6.81932 11.4266C7.30834 12.1252 8.21252 12.9219 9.29096 13.1459C10.275 13.3503 11.6411 13.1262 13.2568 11.3311C13.6263 10.9206 14.2585 10.8873 14.6691 11.2567C15.0796 11.6262 15.1128 12.2585 14.7434 12.669C12.759 14.8738 10.7085 15.4831 8.88421 15.1041C7.15432 14.7448 5.8585 13.5415 5.18085 12.5735C4.86414 12.121 4.97417 11.4975 5.42662 11.1808Z"
                          ></path>
                      </g>
                  </svg>
              </div>
              <div tabindex="0" class="rfu-dropzone" style="position: relative;">
                  <div class="rfu-dropzone__notifier">
                      <div class="rfu-dropzone__inner">
                          <svg width="41" height="41" viewBox="0 0 41 41" xmlns="http://www.w3.org/2000/svg">
                              <path
                                  d="M40.517 28.002V3.997c0-2.197-1.808-3.992-4.005-3.992H12.507a4.004 4.004 0 0 0-3.992 3.993v24.004a4.004 4.004 0 0 0 3.992 3.993h24.005c2.197 0 4.005-1.795 4.005-3.993zm-22.002-7.997l4.062 5.42 5.937-7.423 7.998 10H12.507l6.008-7.997zM.517 8.003V36c0 2.198 1.795 4.005 3.993 4.005h27.997V36H4.51V8.002H.517z"
                                  fill="#000"
                                  fill-rule="nonzero"
                              ></path>
                          </svg>
                          <p>Drag your files here to add to your post</p>
                      </div>
                  </div>
                  <div class="messaging-input__input-wrapper">
                      <div class="rta str-chat__textarea">
                        <textarea rows="1" placeholder="Send a message" class="rta__textarea str-chat__textarea__textarea" style="height: 38px !important;"></textarea></div>
                  </div>
              </div>
              <div class="messaging-input__button" role="button" aria-roledescription="button">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M20 10C20 4.48 15.52 0 10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10ZM6 9H10V6L14 10L10 14V11H6V9Z" fill="white"></path>
                  </svg>
              </div>
          </div>
        </div>

    </div>
  </div>

</div>

<style>
  .messaging-input__button.active {
      opacity: 1;
  }

  .messaging-input__button.active svg path {
      fill: #005fff!important
  }

  .audio{
    max-height: 30px;
  }
  .video{
    max-height: 150px;
  }

  .messaging-input__button.active {
      opacity: 1;
  }

  .messaging-input__button.active svg path {
      fill: #005fff!important
  }
  .str-chat.dark .str-chat__messaging-input{
    color: #fff;
  }
</style>
<script>
  document.addEventListener('livewire:load', function () {
    Livewire.on('scrollToEnd', () => {
        console.log('scrollToEnd');
        let element = document.querySelector(".str-chat__list");
        element.scrollTop = element.scrollHeight;
    })
  })
</script>