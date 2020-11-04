## 用户信息


> 我的信息
~~~
GET: /admin-api/profile
Request: {
}
Response: {
}
~~~

> 信息更新
~~~
PUT: /admin-api/profile/update
Request: {
    'nickname',
    'email',
    'avatar', // 头像为附件ID
}
Response: {
}
~~~

> 修改密码
~~~
PUT: /admin-api/profile/password
Request: {
    'oldpassword', // 密码均 md5 加密后
    'newpassword',
    'newpassword_confirm',
}
Response: {
}
~~~

> 权限列表
~~~
GET: /admin-api/passport/rules
Request: {
}
Response: [
    'list', // 数组列表
]
~~~

