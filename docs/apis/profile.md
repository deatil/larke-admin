## 用户信息


> 我的信息
~~~
GET: /admin-api/profile
Header: {
    'Authorization:Bearer ${accessToken}'
}
Request: {
}
Response: {
}
~~~

> 信息更新
~~~
PUT: /admin-api/profile/update
Header: {
    'Authorization:Bearer ${accessToken}'
}
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
Header: {
    'Authorization:Bearer ${accessToken}'
}
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
Header: {
    'Authorization:Bearer ${accessToken}'
}
Request: {
}
Response: [
    'list', // 数组列表
]
~~~

