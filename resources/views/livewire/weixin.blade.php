<div class="ma">
    @if($msg)
        <h2>{{ $msg }}</h2>
    @endif
    <p> TODO： 本页面，1分钟只准刷新1次 </p>

    @if($qr)
        <img src="{{ $qr }}" alt="">
        
        <p>请在1分钟内使用手机微信扫码此二维码</p>
        <p>扫码成功后，请在“iPad微信登录确认页面”点击登录。</p>
        <p>成功登录后：</p>
        <p>手机会显示：iPad登录，请勿手动退出iPad登录。</p>
        <p>手机可以断网、飞行模式，都无影响</p>
    @endif


    @if($who)
        <h1>当前登录Bot为{{ $teamName }}：{{ $who["nickName"] }}</h1>
        <img src="{{ $who["bigHead"] }}" alt="" width="50px">
        <ul>
            <li>{{ $who["userName"] }}</li>
            <li>{{ $who["signature"] }}</li>
        </ul>
        

        <br>
        <button wire:click="logout">退出Bot登录</button>
        <br>
        <button wire:click="send">主动发送 文本/图片/链接/视频 消息到好友/群</button>
    @endif


</div>
