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
- docker-compose exec app php artisan migrate:fresh --seed

- 有问题重复执行上面2步后，执行下面命令
    - docker-compose exec app php artisan cc
    
- http://yourIp:8080/login
    - Email：admin@admin.com 
    - Password：password

- http://yourIp:8080/channels/wechat
    - 扫码绑定登录


