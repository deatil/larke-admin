## 在 nginx 配置请求转发到后台

~~~
server {
  listen       9527;
  server_name  localhost;
  
  # 接口代理，用于解决跨域问题
  location /admin-api {
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    
    # 后台接口地址
    # proxy_pass http://127.0.0.1:9527/admin-api;
    # proxy_redirect default;
    # add_header Access-Control-Allow-Origin *;
    # add_header Access-Control-Allow-Headers X-Requested-With;
    # add_header Access-Control-Allow-Methods GET,POST,PUT,PATCH,DELETE,OPTIONS;
  }
}
~~~
