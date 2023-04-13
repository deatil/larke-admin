## Nginx 同域名部署前后端分离项目

前后端分离项目，前后端共用一个域名。通过域名后的 url 前缀来区别前后端项目。


~~~cmd
# yourdomain.conf
server
    {
    listen 80;
    server_name yourdomain.com; # 配置项目域名
    index index.html index.htm index.php;

    # 默认访问前端项目
    location / {
        # 前端打包后的静态目录
        alias /path/dist/;
        #解决页面刷新404问题
        try_files $uri $uri/ /index.html;
    }

    # 后端项目
    location ~* ^/(admin-api) {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-INFO-START
    # PHP引用配置，可以注释或修改，写法 1 和写法 2 任意一种都可以
    # 1.宝塔写法 include enable-php-80.conf;
    location ~ \.php(.*)$
    {
        root  /path/public/;
        try_files $uri =404;
        fastcgi_pass  unix:/tmp/php-cgi-80.sock;
        fastcgi_index index.php;
        include fastcgi.conf;
        include pathinfo.conf;
    }
    
    # 2.一般写法,使用 nginx 默认提供的配置，加上 `root` 相关配置即可
    location ~ \.php(.*)$ {
        root  /path/public/;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_split_path_info  ^((?U).+\.php)(/?.+)$;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        fastcgi_param  PATH_INFO  $fastcgi_path_info;
        fastcgi_param  PATH_TRANSLATED  $document_root$fastcgi_path_info;
        include        fastcgi_params;
    }
    #PHP-INFO-END

    # 前端静态资源处理
    location  ^~ /images/ {
        alias /path/dist/images/;
    }

    # 后端静态资源处理
    location  ^~ /vendor/ {
        alias /path/public/vendor/;
    }
    location  ^~ /storage/ {
        alias /path/public/storage/;
    }
}
~~~


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
