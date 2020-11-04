## 用户登陆


> 验证码
~~~
GET: /admin-api/passport/captcha?id=md5(name)
Response: {
    'captcha',
}
~~~

> 登陆
~~~
POST: /admin-api/passport/login
Request: {
    'name': adminname,
    'password': md5(name),
    'captcha': captcha,
}
Response: {
    'access_token', // 鉴权Token
    'expired_in', // access_token过期时间
    'refresh_token', // 刷新Token
}
~~~

> 刷新Token
~~~
PUT: /admin-api/passport/refresh-token
Request: {
    'access_token': accessToken,
    'refresh_token': refreshToken,
}
Response: {
    'access_token', // 鉴权Token
    'expired_in', // access_token过期时间
    'refresh_token', // 刷新Token
}
~~~

> 退出
~~~
POST: /admin-api/passport/logout
Request: {
    'access_token': accessToken, // 未过期的鉴权Token
    'refresh_token': refreshToken, // 未过期的刷新Token
}
Response: {
}
~~~

