<div id="root" wire:poll.7000ms>
  <div id="mobile-channel-list" 
    class="{{$isMobileShowContact?'show':''}}">
    <div class="str-chat str-chat-channel-list messaging {{ $isDarkUi?"dark":"light"  }}">
      <div class="l-header p-4 mb-1">
        <div class="flex justify-between p-4">
          <div class="flex">
            <div data-testid="avatar" class="str-chat__avatar str-chat__avatar--circle" title="holy-dew-9" style="width: 40px; height: 40px; flex-basis: 40px; line-height: 40px; font-size: 20px;">
              <img data-testid="avatar-img" src="{{$seatUserAvatar}}" alt="{{$seatUserName}}" class="str-chat__avatar-image str-chat__avatar-image--loaded" style="width: 40px; height: 40px; flex-basis: 40px; object-fit: cover;">
            </div>
            <div>
              <div class="messaging__channel-list__header__name">{{$seatUserName}}</div>
              <div class="messaging__channel-list__header__name-2">{{$currentTeamName}}</div>
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
                <form class="">
                  <input placeholder="Start typing for suggestions" type="text" class="messaging-create-channel__input
                  border-gray-300 border-indigo-200 ring ring-indigo-100 ring-opacity-40 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full" value=""
                    wire:model.debounce.1000ms="search" >
                    <button type="submit" class="absolute top-0 mt-3 right-4 ml-4">
                      <x-icon.search />
                  </button>
                </form>
              </div>
            </div>
          </header>
          <main>
            <ul class="messaging-create-channel__user-results absolute top-9 mt-3 left-10 ml-4">
              <div>
                @if($search)
                @forelse ($wechatBotContacts as $wechatBotContact)
                  <div class="messaging-create-channel__user-result"
                    wire:click="$set('currentConversionId', {{$wechatBotContact->contact->id}})">
                    <li class="messaging-create-channel__user-result">
                      <div data-testid="avatar" class="str-chat__avatar str-chat__avatar--circle" style="width: 40px; height: 40px; flex-basis: 40px; line-height: 40px; font-size: 20px;">
                        <img data-testid="avatar-imgs" src="{{ $wechatBotContact->contact->smallHead?:$defaultAvatar }}" style="width: 40px; height: 40px; flex-basis: 40px; object-fit: cover;">
                      </div>
                      <div class="messaging-create-channel__user-result__details">
                        <span>{{$wechatBotContact->remark}}</span>
                      </div>
                    </li>
                  </div>
                @empty
                    <p>No wechatBotContacts</p>
                @endforelse
                @endif
              </div>
            </ul>
          </main>
        </div>
      </div>
      <div class="messaging__channel-list">
        @foreach ($conversions as $contactId => $conversion)
        <div wire:click="$set('currentConversionId', {{$contactId}})" id="c-{{$contactId}}" class="channel-preview__container {{ $currentConversionId===$contactId?'selected':'' }} ">
          <div class="channel-preview__avatars">
            <img data-testid="avatar-img" src="{{$conversion[0]['contact']['smallHead']?:$defaultAvatar}}" alt="" class="str-chat__avatar-image str-chat__avatar-image--loaded">
          </div>
          <div class="channel-preview__content-wrapper">
            <div class="channel-preview__content-top">
              <p class="channel-preview__content-name">{{$conversion[0]['contact']['nickName']?:'新加入群'.$conversion[0]['contact']['id'] }}</p>
              <p class="channel-preview__content-time">{{ Illuminate\Support\Carbon::parse($conversion[0]['updated_at'])->diffForHumans() }}</p>
            </div>
            <p class="channel-preview__content-message">{{ $conversion[0]['content']['content']??'有消息🆕' }}</p>
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
                <div class="messaging-create-channel__users" 
                  style="display: none">
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
              <img data-testid="avatar-img" src="{{ $conversions[$currentConversionId][0]['contact']['smallHead']?:$defaultAvatar }}" alt="" class="str-chat__avatar-image str-chat__avatar-image--loaded">
            </div>
            <div class="channel-header__name">{{ $conversions[$currentConversionId][0]['contact']['nickName']?:'暂无群名'.$conversions[$currentConversionId][0]['contact']['id'] }}</div>
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
                <li class="hidden">
                  <div class="str-chat__date-separator">
                    <hr class="str-chat__date-separator-line">
                    <div class="str-chat__date-separator-date">Today at 5:13 AM</div>
                  </div>
                </li>

                @foreach (array_reverse($conversions[$currentConversionId]) as $conversion)
                <li class="str-chat__li str-chat__li--single" id="conversion-{{$conversion['id']}}">
                  <div 
                    class="str-chat__message str-chat__message-simple str-chat__message--regular str-chat__message--received str-chat__message--has-text 
                    {{ $conversion['seat_user_id'] ?'str-chat__message--me str-chat__message-simple--me':'' }} 
                    ">
                    
                    @if($conversion['seat_user_id'])
                      <span style="display: block" class="str-chat__message-simple-status" data-testid="message-status-received">
                        <div class="str-chat__tooltip">Delivered</div>
                        <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm3.72 6.633a.955.955 0 1 0-1.352-1.352L6.986 8.663 5.633 7.31A.956.956 0 1 0 4.28 8.663l2.029 2.028a.956.956 0 0 0 1.353 0l4.058-4.058z" fill="#006CFF" fill-rule="evenodd"></path>
                        </svg>
                      </span>

                      <div style="display: block" class="str-chat__avatar str-chat__avatar--circle" title="solitary-shadow-5" 
                        style="width: 32px; height: 32px; flex-basis: 32px; line-height: 32px; font-size: 16px;">
                        <img data-testid="avatar-img" 
                          src="{{$conversion['seat']['profile_photo_url']}}"
                          alt="s"
                          class="str-chat__avatar-image str-chat__avatar-image--loaded" 
                          style="width: 32px; height: 32px; flex-basis: 32px; object-fit: cover;">
                      </div>
                    @else
                    <div style="display: block" class="str-chat__avatar str-chat__avatar--circle" title="solitary-shadow-5" style="width: 32px; height: 32px; flex-basis: 32px; line-height: 32px; font-size: 16px;
                    {{ $isRoom?'':'display:none' }}">
                      <img data-testid="avatar-img" 
                        src="{{ $conversion['from'] 
                          ? ($conversion['from']['smallHead']?:$defaultAvatar) 
                          : ($conversion['contact']['smallHead']?:$defaultAvatar) }}"
                        alt="s"
                        class="str-chat__avatar-image str-chat__avatar-image--loaded" 
                        style="width: 32px; height: 32px; flex-basis: 32px; object-fit: cover;">
                    </div>
                    @endif

                    @php
                      // TODO   处理 content   
                    @endphp
                    <div data-testid="message-inner" class="str-chat__message-inner">
                      <div class="str-chat__message-text">
                        <div data-testid="message-text-inner-wrapper" class="str-chat__message-text-inner str-chat__message-simple-text-inner">
                          <p>{{$conversion['content']['content']??'暂未处理消息'}}</p>
                        </div>
                        <div data-testid="message-options" class="str-chat__message-simple__actions">
                          <div data-testid="message-reaction-action" class="str-chat__message-simple__actions__action str-chat__message-simple__actions__action--reactions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12">
                              <g fill-rule="evenodd" clip-rule="evenodd">
                                <path d="M6 1.2C3.3 1.2 1.2 3.3 1.2 6c0 2.7 2.1 4.8 4.8 4.8 2.7 0 4.8-2.1 4.8-4.8 0-2.7-2.1-4.8-4.8-4.8zM0 6c0-3.3 2.7-6 6-6s6 2.7 6 6-2.7 6-6 6-6-2.7-6-6z">
                                </path>
                                <path d="M5.4 4.5c0 .5-.4.9-.9.9s-.9-.4-.9-.9.4-.9.9-.9.9.4.9.9zM8.4 4.5c0 .5-.4.9-.9.9s-.9-.4-.9-.9.4-.9.9-.9.9.4.9.9zM3.3 6.7c.3-.2.6-.1.8.1.3.4.8.9 1.5 1 .6.2 1.4.1 2.4-1 .2-.2.6-.3.8 0 .2.2.3.6 0 .8-1.1 1.3-2.4 1.7-3.5 1.5-1-.2-1.8-.9-2.2-1.5-.2-.3-.1-.7.2-.9z">
                                </path>
                              </g>
                            </svg>
                          </div>
                          <div data-testid="thread-action" class="str-chat__message-simple__actions__action str-chat__message-simple__actions__action--thread">
                            <svg width="14" height="10" xmlns="http://www.w3.org/2000/svg">
                              <path d="M8.516 3c4.78 0 4.972 6.5 4.972 6.5-1.6-2.906-2.847-3.184-4.972-3.184v2.872L3.772 4.994 8.516.5V3zM.484 5l4.5-4.237v1.78L2.416 5l2.568 2.125v1.828L.484 5z" fill-rule="evenodd">
                              </path>
                            </svg>
                          </div>
                          <div data-testid="message-actions" class="str-chat__message-simple__actions__action str-chat__message-simple__actions__action--options">
                            <div data-testid="message-actions-box" class="str-chat__message-actions-box">
                              <ul class="str-chat__message-actions-list">
                                <button>
                                  <li class="str-chat__message-actions-list-item">Flag</li>
                                </button>
                                <button>
                                  <li class="str-chat__message-actions-list-item">Mute</li>
                                </button>
                              </ul>
                            </div>
                            <svg width="11" height="4" viewBox="0 0 11 4" xmlns="http://www.w3.org/2000/svg">
                              <path d="M1.5 3a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm4 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm4 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z" fill-rule="nonzero">
                              </path>
                            </svg>
                          </div>
                        </div>
                      </div>
                      
                      <div class="str-chat__message-data str-chat__message-simple-data">
                        <span class="str-chat__message-simple-name {{ $isRoom?'':'hidden'}}"">{{ $conversion['from'] ? $conversion['from']['nickName'] : ($conversion['seat_user_id']?$conversion['seat']['name']:$conversion['contact']['nickName']) }}</span>
                        <time class="str-chat__message-simple-timestamp" datetime="Mon Mar 15 2021 05:13:11 GMT+0800 (China Standard Time)" title="Mon Mar 15 2021 05:13:11 GMT+0800 (China Standard Time)">{{ str_replace('T', ' ', substr($conversion['updated_at'],0,16)) }}</time>
                      </div>
                    </div>
                  </div>
                </li>
                @endforeach
              
              </ul>
              <div>
              </div>
            </div>
          </div>

          <div class="str-chat__list-notifications">
            <button data-testid="message-notification" class="str-chat__message-notification">New Messages!</button>
          </div>
          
          <div class="str-chat__messaging-input">
              <div class="messaging-input__button emoji-button" role="button" aria-roledescription="button"
                wire:click="$toggle('isEmojiPickerOpen')">
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
                      
                    <div 
                      style="display: none" class="rfu-image-previewer"><div class="rfu-image-previewer__image rfu-image-previewer__image--loaded"><div class="rfu-thumbnail__wrapper" style="width: 100px; height: 100px;"><div class="rfu-thumbnail__overlay"><div class="rfu-icon-button" role="button"><div><svg width="28" height="28" viewBox="0 0 28 28" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><defs><path d="M465 5c5.53 0 10 4.47 10 10s-4.47 10-10 10-10-4.47-10-10 4.47-10 10-10zm3.59 5L465 13.59 461.41 10 460 11.41l3.59 3.59-3.59 3.59 1.41 1.41 3.59-3.59 3.59 3.59 1.41-1.41-3.59-3.59 3.59-3.59-1.41-1.41z" id="b"></path><filter x="-30%" y="-30%" width="160%" height="160%" filterUnits="objectBoundingBox" id="a"><feOffset in="SourceAlpha" result="shadowOffsetOuter1"></feOffset><feGaussianBlur stdDeviation="2" in="shadowOffsetOuter1" result="shadowBlurOuter1"></feGaussianBlur><feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.5 0" in="shadowBlurOuter1"></feColorMatrix></filter></defs><g transform="translate(-451 -1)" fill-rule="nonzero" fill="none"><use fill="#000" filter="url(#a)" xlink:href="#b"></use><use fill="#FFF" fill-rule="evenodd" xlink:href="#b"></use></g></svg></div></div></div><img src="{{$defaultAvatar}}" class="rfu-thumbnail__image" alt=""></div></div><div class="rfu-image-upload-button"><label><input type="file" class="rfu-image-input" accept="image/*" multiple=""><div role="button" class="rfu-thumbnail-placeholder"><svg width="14" height="15" viewBox="0 0 14 15" xmlns="http://www.w3.org/2000/svg"><path d="M14 8.998H8v6H6v-6H0v-2h6v-6h2v6h6z" fill="#A0B2B8" fill-rule="nonzero"></path></svg></div></label></div></div>

                      <div class="rta str-chat__textarea">
                        <textarea
                          wire:model="content" 
                          wire:keydown.enter.prevent="send"
                          rows="1" placeholder="Send a message" class="rta__textarea str-chat__textarea__textarea" spellcheck="false" style="height: 38px !important;"></textarea>
                      </div>
                  </div>
              </div>

              <div wire:click="send" class="messaging-input__button" role="button" aria-roledescription="button">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M20 10C20 4.48 15.52 0 10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10ZM6 9H10V6L14 10L10 14V11H6V9Z" fill="white"></path>
                  </svg>
              </div>
              @if($isEmojiPickerOpen)
                <div class="str-chat__input--emojipicker"><section class="emoji-mart emoji-mart-light" aria-label="Pick your emoji" style="width: 338px;"><div class="emoji-mart-bar"><nav class="emoji-mart-anchors" aria-label="Emoji categories"><button aria-label="Frequently Used" title="Frequently Used" data-index="1" type="button" class="emoji-mart-anchor emoji-mart-anchor-selected" style="color: rgb(0, 108, 255);"><div class="emoji-mart-anchor-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M13 4h-2l-.001 7H9v2h2v2h2v-2h4v-2h-4z"></path><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0m0 22C6.486 22 2 17.514 2 12S6.486 2 12 2s10 4.486 10 10-4.486 10-10 10"></path></svg></div><span class="emoji-mart-anchor-bar" style="background-color: rgb(0, 108, 255);"></span></button><button aria-label="Smileys &amp; People" title="Smileys &amp; People" data-index="2" type="button" class="emoji-mart-anchor " style=""><div class="emoji-mart-anchor-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0m0 22C6.486 22 2 17.514 2 12S6.486 2 12 2s10 4.486 10 10-4.486 10-10 10"></path><path d="M8 7a2 2 0 1 0-.001 3.999A2 2 0 0 0 8 7M16 7a2 2 0 1 0-.001 3.999A2 2 0 0 0 16 7M15.232 15c-.693 1.195-1.87 2-3.349 2-1.477 0-2.655-.805-3.347-2H15m3-2H6a6 6 0 1 0 12 0"></path></svg></div><span class="emoji-mart-anchor-bar" style="background-color: rgb(0, 108, 255);"></span></button><button aria-label="Animals &amp; Nature" title="Animals &amp; Nature" data-index="3" type="button" class="emoji-mart-anchor " style=""><div class="emoji-mart-anchor-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M15.5 8a1.5 1.5 0 1 0 .001 3.001A1.5 1.5 0 0 0 15.5 8M8.5 8a1.5 1.5 0 1 0 .001 3.001A1.5 1.5 0 0 0 8.5 8"></path><path d="M18.933 0h-.027c-.97 0-2.138.787-3.018 1.497-1.274-.374-2.612-.51-3.887-.51-1.285 0-2.616.133-3.874.517C7.245.79 6.069 0 5.093 0h-.027C3.352 0 .07 2.67.002 7.026c-.039 2.479.276 4.238 1.04 5.013.254.258.882.677 1.295.882.191 3.177.922 5.238 2.536 6.38.897.637 2.187.949 3.2 1.102C8.04 20.6 8 20.795 8 21c0 1.773 2.35 3 4 3 1.648 0 4-1.227 4-3 0-.201-.038-.393-.072-.586 2.573-.385 5.435-1.877 5.925-7.587.396-.22.887-.568 1.104-.788.763-.774 1.079-2.534 1.04-5.013C23.929 2.67 20.646 0 18.933 0M3.223 9.135c-.237.281-.837 1.155-.884 1.238-.15-.41-.368-1.349-.337-3.291.051-3.281 2.478-4.972 3.091-5.031.256.015.731.27 1.265.646-1.11 1.171-2.275 2.915-2.352 5.125-.133.546-.398.858-.783 1.313M12 22c-.901 0-1.954-.693-2-1 0-.654.475-1.236 1-1.602V20a1 1 0 1 0 2 0v-.602c.524.365 1 .947 1 1.602-.046.307-1.099 1-2 1m3-3.48v.02a4.752 4.752 0 0 0-1.262-1.02c1.092-.516 2.239-1.334 2.239-2.217 0-1.842-1.781-2.195-3.977-2.195-2.196 0-3.978.354-3.978 2.195 0 .883 1.148 1.701 2.238 2.217A4.8 4.8 0 0 0 9 18.539v-.025c-1-.076-2.182-.281-2.973-.842-1.301-.92-1.838-3.045-1.853-6.478l.023-.041c.496-.826 1.49-1.45 1.804-3.102 0-2.047 1.357-3.631 2.362-4.522C9.37 3.178 10.555 3 11.948 3c1.447 0 2.685.192 3.733.57 1 .9 2.316 2.465 2.316 4.48.313 1.651 1.307 2.275 1.803 3.102.035.058.068.117.102.178-.059 5.967-1.949 7.01-4.902 7.19m6.628-8.202c-.037-.065-.074-.13-.113-.195a7.587 7.587 0 0 0-.739-.987c-.385-.455-.648-.768-.782-1.313-.076-2.209-1.241-3.954-2.353-5.124.531-.376 1.004-.63 1.261-.647.636.071 3.044 1.764 3.096 5.031.027 1.81-.347 3.218-.37 3.235"></path></svg></div><span class="emoji-mart-anchor-bar" style="background-color: rgb(0, 108, 255);"></span></button><button aria-label="Food &amp; Drink" title="Food &amp; Drink" data-index="4" type="button" class="emoji-mart-anchor " style=""><div class="emoji-mart-anchor-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M17 4.978c-1.838 0-2.876.396-3.68.934.513-1.172 1.768-2.934 4.68-2.934a1 1 0 0 0 0-2c-2.921 0-4.629 1.365-5.547 2.512-.064.078-.119.162-.18.244C11.73 1.838 10.798.023 9.207.023 8.579.022 7.85.306 7 .978 5.027 2.54 5.329 3.902 6.492 4.999 3.609 5.222 0 7.352 0 12.969c0 4.582 4.961 11.009 9 11.009 1.975 0 2.371-.486 3-1 .629.514 1.025 1 3 1 4.039 0 9-6.418 9-11 0-5.953-4.055-8-7-8M8.242 2.546c.641-.508.943-.523.965-.523.426.169.975 1.405 1.357 3.055-1.527-.629-2.741-1.352-2.98-1.846.059-.112.241-.356.658-.686M15 21.978c-1.08 0-1.21-.109-1.559-.402l-.176-.146c-.367-.302-.816-.452-1.266-.452s-.898.15-1.266.452l-.176.146c-.347.292-.477.402-1.557.402-2.813 0-7-5.389-7-9.009 0-5.823 4.488-5.991 5-5.991 1.939 0 2.484.471 3.387 1.251l.323.276a1.995 1.995 0 0 0 2.58 0l.323-.276c.902-.78 1.447-1.251 3.387-1.251.512 0 5 .168 5 6 0 3.617-4.187 9-7 9"></path></svg></div><span class="emoji-mart-anchor-bar" style="background-color: rgb(0, 108, 255);"></span></button><button aria-label="Activity" title="Activity" data-index="5" type="button" class="emoji-mart-anchor " style=""><div class="emoji-mart-anchor-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 0C5.373 0 0 5.372 0 12c0 6.627 5.373 12 12 12 6.628 0 12-5.373 12-12 0-6.628-5.372-12-12-12m9.949 11H17.05c.224-2.527 1.232-4.773 1.968-6.113A9.966 9.966 0 0 1 21.949 11M13 11V2.051a9.945 9.945 0 0 1 4.432 1.564c-.858 1.491-2.156 4.22-2.392 7.385H13zm-2 0H8.961c-.238-3.165-1.536-5.894-2.393-7.385A9.95 9.95 0 0 1 11 2.051V11zm0 2v8.949a9.937 9.937 0 0 1-4.432-1.564c.857-1.492 2.155-4.221 2.393-7.385H11zm4.04 0c.236 3.164 1.534 5.893 2.392 7.385A9.92 9.92 0 0 1 13 21.949V13h2.04zM4.982 4.887C5.718 6.227 6.726 8.473 6.951 11h-4.9a9.977 9.977 0 0 1 2.931-6.113M2.051 13h4.9c-.226 2.527-1.233 4.771-1.969 6.113A9.972 9.972 0 0 1 2.051 13m16.967 6.113c-.735-1.342-1.744-3.586-1.968-6.113h4.899a9.961 9.961 0 0 1-2.931 6.113"></path></svg></div><span class="emoji-mart-anchor-bar" style="background-color: rgb(0, 108, 255);"></span></button><button aria-label="Travel &amp; Places" title="Travel &amp; Places" data-index="6" type="button" class="emoji-mart-anchor " style=""><div class="emoji-mart-anchor-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M6.5 12C5.122 12 4 13.121 4 14.5S5.122 17 6.5 17 9 15.879 9 14.5 7.878 12 6.5 12m0 3c-.275 0-.5-.225-.5-.5s.225-.5.5-.5.5.225.5.5-.225.5-.5.5M17.5 12c-1.378 0-2.5 1.121-2.5 2.5s1.122 2.5 2.5 2.5 2.5-1.121 2.5-2.5-1.122-2.5-2.5-2.5m0 3c-.275 0-.5-.225-.5-.5s.225-.5.5-.5.5.225.5.5-.225.5-.5.5"></path><path d="M22.482 9.494l-1.039-.346L21.4 9h.6c.552 0 1-.439 1-.992 0-.006-.003-.008-.003-.008H23c0-1-.889-2-1.984-2h-.642l-.731-1.717C19.262 3.012 18.091 2 16.764 2H7.236C5.909 2 4.738 3.012 4.357 4.283L3.626 6h-.642C1.889 6 1 7 1 8h.003S1 8.002 1 8.008C1 8.561 1.448 9 2 9h.6l-.043.148-1.039.346a2.001 2.001 0 0 0-1.359 2.097l.751 7.508a1 1 0 0 0 .994.901H3v1c0 1.103.896 2 2 2h2c1.104 0 2-.897 2-2v-1h6v1c0 1.103.896 2 2 2h2c1.104 0 2-.897 2-2v-1h1.096a.999.999 0 0 0 .994-.901l.751-7.508a2.001 2.001 0 0 0-1.359-2.097M6.273 4.857C6.402 4.43 6.788 4 7.236 4h9.527c.448 0 .834.43.963.857L19.313 9H4.688l1.585-4.143zM7 21H5v-1h2v1zm12 0h-2v-1h2v1zm2.189-3H2.811l-.662-6.607L3 11h18l.852.393L21.189 18z"></path></svg></div><span class="emoji-mart-anchor-bar" style="background-color: rgb(0, 108, 255);"></span></button><button aria-label="Objects" title="Objects" data-index="7" type="button" class="emoji-mart-anchor " style=""><div class="emoji-mart-anchor-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 0a9 9 0 0 0-5 16.482V21s2.035 3 5 3 5-3 5-3v-4.518A9 9 0 0 0 12 0zm0 2c3.86 0 7 3.141 7 7s-3.14 7-7 7-7-3.141-7-7 3.14-7 7-7zM9 17.477c.94.332 1.946.523 3 .523s2.06-.19 3-.523v.834c-.91.436-1.925.689-3 .689a6.924 6.924 0 0 1-3-.69v-.833zm.236 3.07A8.854 8.854 0 0 0 12 21c.965 0 1.888-.167 2.758-.451C14.155 21.173 13.153 22 12 22c-1.102 0-2.117-.789-2.764-1.453z"></path><path d="M14.745 12.449h-.004c-.852-.024-1.188-.858-1.577-1.824-.421-1.061-.703-1.561-1.182-1.566h-.009c-.481 0-.783.497-1.235 1.537-.436.982-.801 1.811-1.636 1.791l-.276-.043c-.565-.171-.853-.691-1.284-1.794-.125-.313-.202-.632-.27-.913-.051-.213-.127-.53-.195-.634C7.067 9.004 7.039 9 6.99 9A1 1 0 0 1 7 7h.01c1.662.017 2.015 1.373 2.198 2.134.486-.981 1.304-2.058 2.797-2.075 1.531.018 2.28 1.153 2.731 2.141l.002-.008C14.944 8.424 15.327 7 16.979 7h.032A1 1 0 1 1 17 9h-.011c-.149.076-.256.474-.319.709a6.484 6.484 0 0 1-.311.951c-.429.973-.79 1.789-1.614 1.789"></path></svg></div><span class="emoji-mart-anchor-bar" style="background-color: rgb(0, 108, 255);"></span></button><button aria-label="Symbols" title="Symbols" data-index="8" type="button" class="emoji-mart-anchor " style=""><div class="emoji-mart-anchor-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M0 0h11v2H0zM4 11h3V6h4V4H0v2h4zM15.5 17c1.381 0 2.5-1.116 2.5-2.493s-1.119-2.493-2.5-2.493S13 13.13 13 14.507 14.119 17 15.5 17m0-2.986c.276 0 .5.222.5.493 0 .272-.224.493-.5.493s-.5-.221-.5-.493.224-.493.5-.493M21.5 19.014c-1.381 0-2.5 1.116-2.5 2.493S20.119 24 21.5 24s2.5-1.116 2.5-2.493-1.119-2.493-2.5-2.493m0 2.986a.497.497 0 0 1-.5-.493c0-.271.224-.493.5-.493s.5.222.5.493a.497.497 0 0 1-.5.493M22 13l-9 9 1.513 1.5 8.99-9.009zM17 11c2.209 0 4-1.119 4-2.5V2s.985-.161 1.498.949C23.01 4.055 23 6 23 6s1-1.119 1-3.135C24-.02 21 0 21 0h-2v6.347A5.853 5.853 0 0 0 17 6c-2.209 0-4 1.119-4 2.5s1.791 2.5 4 2.5M10.297 20.482l-1.475-1.585a47.54 47.54 0 0 1-1.442 1.129c-.307-.288-.989-1.016-2.045-2.183.902-.836 1.479-1.466 1.729-1.892s.376-.871.376-1.336c0-.592-.273-1.178-.818-1.759-.546-.581-1.329-.871-2.349-.871-1.008 0-1.79.293-2.344.879-.556.587-.832 1.181-.832 1.784 0 .813.419 1.748 1.256 2.805-.847.614-1.444 1.208-1.794 1.784a3.465 3.465 0 0 0-.523 1.833c0 .857.308 1.56.924 2.107.616.549 1.423.823 2.42.823 1.173 0 2.444-.379 3.813-1.137L8.235 24h2.819l-2.09-2.383 1.333-1.135zm-6.736-6.389a1.02 1.02 0 0 1 .73-.286c.31 0 .559.085.747.254a.849.849 0 0 1 .283.659c0 .518-.419 1.112-1.257 1.784-.536-.651-.805-1.231-.805-1.742a.901.901 0 0 1 .302-.669M3.74 22c-.427 0-.778-.116-1.057-.349-.279-.232-.418-.487-.418-.766 0-.594.509-1.288 1.527-2.083.968 1.134 1.717 1.946 2.248 2.438-.921.507-1.686.76-2.3.76"></path></svg></div><span class="emoji-mart-anchor-bar" style="background-color: rgb(0, 108, 255);"></span></button><button aria-label="Flags" title="Flags" data-index="9" type="button" class="emoji-mart-anchor " style=""><div class="emoji-mart-anchor-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M0 0l6.084 24H8L1.916 0zM21 5h-4l-1-4H4l3 12h3l1 4h13L21 5zM6.563 3h7.875l2 8H8.563l-2-8zm8.832 10l-2.856 1.904L12.063 13h3.332zM19 13l-1.5-6h1.938l2 8H16l3-2z"></path></svg></div><span class="emoji-mart-anchor-bar" style="background-color: rgb(0, 108, 255);"></span></button></nav></div><div class="emoji-mart-scroll"><section class="emoji-mart-category" aria-label="Frequently Used"><div data-name="Recent" class="emoji-mart-category-label"><span aria-hidden="true">Frequently Used</span></div><ul class="emoji-mart-category-list"><li><button aria-label="👍🏼, +1, thumbsup" class="emoji-mart-emoji emoji-mart-emoji-native" type="button"><span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">👍🏼</span></button></li><li><button aria-label="😀, grinning" class="emoji-mart-emoji emoji-mart-emoji-native" type="button"><span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">😀</span></button></li><li><button aria-label="😘, kissing_heart" class="emoji-mart-emoji emoji-mart-emoji-native" type="button"><span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">😘</span></button></li><li><button aria-label="😍, heart_eyes" class="emoji-mart-emoji emoji-mart-emoji-native" type="button"><span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">😍</span></button></li><li><button aria-label="😜, stuck_out_tongue_winking_eye" class="emoji-mart-emoji emoji-mart-emoji-native" type="button"><span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">😜</span></button></li><li><button aria-label="😆, laughing, satisfied" class="emoji-mart-emoji emoji-mart-emoji-native" type="button"><span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">😆</span></button></li><li><button aria-label="😅, sweat_smile" class="emoji-mart-emoji emoji-mart-emoji-native" type="button"><span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">😅</span></button></li><li><button aria-label="😂, joy" class="emoji-mart-emoji emoji-mart-emoji-native" type="button"><span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">😂</span></button></li><li><button aria-label="😐, neutral_face" class="emoji-mart-emoji emoji-mart-emoji-native" type="button"><span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">😐</span></button></li><li><button aria-label="🤫, shushing_face, face_with_finger_covering_closed_lips" class="emoji-mart-emoji emoji-mart-emoji-native" type="button"><span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">🤫</span></button></li><li><button aria-label="🤥, lying_face" class="emoji-mart-emoji emoji-mart-emoji-native" type="button"><span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">🤥</span></button></li><li><button aria-label="😱, scream" class="emoji-mart-emoji emoji-mart-emoji-native" type="button"><span style="font-size: 24px; display: inline-block; width: 24px; height: 24px; word-break: keep-all;">😱</span></button></li></ul></section></div></section></div>
              @endif
          </div>
        </div>
      @endif

      @if($isThread)
        <div class="str-chat__thread" >
          <div class="custom-thread-header">
              <div class="custom-thread-header__left">
                  <p class="custom-thread-header__left-title">Thread</p>
                  <p class="custom-thread-header__left-count"></p>
              </div>
              <svg wire:click="$toggle('isThread')" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" style="cursor: pointer; margin-right: 10px;">
                  <path d="M0 20C0 8.95431 8.95431 0 20 0C31.0457 0 40 8.95431 40 20C40 31.0457 31.0457 40 20 40C8.95431 40 0 31.0457 0 20Z" fill="#3E3E41"></path>
                  <path
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M27.5625 25.4416L22.1208 20L27.5625 14.5583C28.15 13.9708 28.15 13.025 27.5625 12.4375C26.975 11.85 26.0291 11.85 25.4416 12.4375L20 17.8791L14.5583 12.4375C13.9708 11.85 13.025 11.85 12.4375 12.4375C11.85 13.025 11.85 13.9708 12.4375 14.5583L17.8791 20L12.4375 25.4416C11.85 26.0291 11.85 26.975 12.4375 27.5625C13.025 28.15 13.9708 28.15 14.5583 27.5625L20 22.1208L25.4416 27.5625C26.0291 28.15 26.975 28.15 27.5625 27.5625C28.1458 26.975 28.1458 26.025 27.5625 25.4416Z"
                      fill="#E9E9EA"
                  ></path>
              </svg>
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
                          <time class="str-chat__message-simple-timestamp" datetime="Mon Mar 15 2021 15:41:38 GMT+0800 (China Standard Time)" title="Mon Mar 15 2021 15:41:38 GMT+0800 (China Standard Time)">Today at 3:41 PM</time>
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
      @endif

    </div>
  </div>

</div>

<style>

@media screen and (max-width: 640px) {
  .messaging__channel-header__avatars {
    margin-left: 10px;
  }
  .str-chat-channel .str-chat__container{
    padding: 0;
  }
  .messaging.str-chat .str-chat__thread{
    display: none;
  }
}
.messaging__channel-list__header__name-2{
  color: #999;
}

.str-chat__list{
  scroll-behavior: smooth;
}
</style>

<script>
  document.addEventListener('livewire:load', function () {
    Livewire.on('scrollToEnd', () => {
        let element = document.querySelector(".str-chat__list");
        element.scrollTop = element.scrollHeight;
    })
  })
</script>