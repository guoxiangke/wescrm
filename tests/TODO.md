# 测试

- 非好友，在bot的群里发消息，保存 wechatBotContact.type = WechatContact::TYPES['stranger']
- 陌生人请求添加bot
    - 存储 陌生人信息 到 wechatContact
    - 如果 已有，则更改为 WechatContact::TYPES['friend']
    - 保存 wechatBotContact
