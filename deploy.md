# Deploy Guide

- git clone https://github.com/guoxiangke/wescrm.git
- cd wescrm
- cp .env.example .env.docker
    - 编辑 .env.docker，填写weiju手机号和密码，和地址端口
        - WEIJU_PHONE=
        - WEIJU_PASSWORD=
        - WEIJU_ENDPOINT=""
- docker pull guoxiangke/wescrm
- docker-compose up -d
- docker-compose exec app php artisan key:generate
- docker-compose exec app php artisan migrate:fresh --seed  # 等待 docker-compose up -d 启动完成后执行，约1分钟

- 有问题重复执行上面2步后，执行下面命令
    - docker-compose exec app php artisan cc
    - 默认暴露8080端口，请使用反代配置https证书访问

- http://yourIp:8080/login
    - Email：admin@admin.com 
    - Password：password

- http://yourIp:8080/channels/wechat
    - 扫码绑定登录


## 功能简介与使用流程

- 新注册用户，默认拥有1个bot登录名额，成为bot管理者
    - 前提是我的weiju token有多余登录数量
- bot管理者可以添加多个座席用户，共同管理bot
    - 座席用户默认3天有效期
- 第一次扫码绑定登录
    - 绑定后，不可以切换，除非新建bot管理者用户
    - 绑定后，自动弹窗确认登录
- 通讯录管理
    - 给好友加标签
    - 给好友改备注
    - 按标签批量发送内容
    - 标签和备注都在本系统中，不操作手机微信的好友
- 内容/素材库
    - 主动发送类型：
        - 带有模版变量的文本
            - :name 好友昵称  
            - :remark 备注（手机微信已设置的备注，如无设置，默认为昵称）
            - :seat 客服座席名字: 
            - :no 第x号好友
        - 文本
        - 图片
        - 视频
        - 名片
        - 链接
- 关键词自动回复（基础版本）
    - hi* 代表以hi开头的
    - *hi   代表以hi结尾的
    - *hi* 代表包含hi的
    - *hi*thanks* 代表先hi后thanks的
- 图灵机器人聊天
    - 需要配置 key 和 id
- 消息处理
    - 默认不监听群消息/和公众号消息（可以选择开启）
    - 开放消息API （把本系统对weiju的raw content处理后的消息Post到开发者的webhook上）
        ```
        {
            "id" : 7,
            "seat_user_id" : 1, //座席用户id
            "content" : //内容 array
            {
                "content" : "主动发送 文本/链接/名片/图片/视频 消息到好友/群"
            },
            "type" : 'text', 
            "from_contact_id" : 516, //@see contact
            "msgType" : 1,
            "created_at" : "2021-03-11T02:03:54.000000Z",
            "contact" :
            {
                "id" : 516,
                "userName" : "bluesky_still",
                "nickName" : "天空蔚蓝",
                "aliasName" : null,
                "sex" : 0,
                "country" : "CN",
                "province" : null,
                "city" : null,
                "signature" : null,
                "bigHead" : "https://wx.qlogo.cn/mmhead/ver_1/YWTnltiadZVeiaNIN4ic2d6fHDUaGWh2GDicc8E4bTic3UBp6iaBRibPQica3U3SpDfXW2YjeibhSVTUEY5373dwuJEb1SQ/0",
                "smallHead" : "https://wx.qlogo.cn/mmhead/ver_1/YWTnltiadZVeiaNIN4ic2d6fHDUaGWh2GDicc8E4bTic3UBp6iaBRibPQica3U3SpDfXW2YjeibhSVTUEY5373dwuJEb1SQ/132",
                "created_at" : "2021-03-10T23:23:24.000000Z"
            }
            }
        ```
    - 高级开发者选项 直接转发weiju的raw content给高级开发者
- 开放API 
    - 创建Token http://yourIp:8080/user/api-tokens
    - http://yourIp:8080/api/wechat/send
        - 'Authorization: Bearer yourApiToken',
        - 'Content-Type: application/json'
        - 发送内容：
        ```
        {
            "type": "text",
            "to": "bluesky_still",
            "data": {
                "content": "API2主动发送 文本/链接/名片/图片/视频 消息到好友/群"
            }
        }

        {
            "type": "image",
            "to": "bluesky_still",
            "data": {
                "content": "https://www.upyun.com/static/logos/dangdang.png"
            }
        }


        {
            "type": "video",
            "to": "bluesky_still",
            "data": {
                "path": "https://yourdomain.com/test.mp4",
                "thumbPath": "https://www.upyun.com/static/logos/dangdang.png"
            }
        }


        {
            "type": "url",
            "to": "bluesky_still",
            "data": {
                "url": "https://weibo.com",
                "title": "测试链接到百度",
                "thumbUrl": "https://www.upyun.com/static/logos/dangdang.png",
                "description": "其实我放的是微博的链接"
            }
        }

        {
            "type": "card",
            "to": "bluesky_still",
            "data": {
                "nickName": "nothing",
                "nameCardId": "bluesky_still"
            }
        }

        ```

## TODO

- 网页版微信，多座席回复
- 座席用户默认3天有效期
    - 过期付费，即按座席数量付费使用
- 定时发送任务
- 关键词自动回复（正则高级版本）