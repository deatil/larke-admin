## larke-admin 后台快速开发框架


### 项目介绍

*  `larke-admin` 是基于 `laravel8` 版本的后台快速开发框架，完全api接口化，适用于前后端分离的项目
*  基于 `JWT` 的用户登陆态管理
*  权限判断基于 `php-casbin` 的 `RBAC` 授权


### 必须

 - PHP >= 7.3.0
 - Laravel >= 8.0.0
 - Fileinfo PHP Extension


### 安装步骤

首先安装 `laravel 8.*`，并确认连接数据库的配置没有问题，开始执行以下命令

```php
composer require lake/larke-admin
```

然后运行下面的命令，推送配置文件

```php
php artisan vendor:publish --tag=larke-admin-config
```

运行完命令后，你可以找到 `config/larkeadmin.php`、`config/larkeauth.php` 及 `config/larkeauth-rbac-model.conf` 三个配置文件

最后运行下面的命令安装完成系统

```php
php artisan larke-admin:install
```

你可能第一次安装需要运行以下命令导入路由权限规则

```php
php artisan larke-admin:import-route
```

如果遇到跨域问题，你可以修改官方的配置文件 `config/cors.php`，在 `paths` 列表增加系统接口前缀 `admin-api/*`

如果官方没有配置，你也可以在 `App\Http\Kernel->middleware` 属性添加

```php
\Larke\Admin\Middleware\RequestOptions::class,
```


### 特别鸣谢

感谢以下的项目,排名不分先后

laravel/framework

lcobucci/jwt

casbin/casbin

composer/semver


### 开源协议

*  `larke-admin` 遵循 `Apache2` 开源协议发布，在保留本系统版权的情况下提供个人及商业免费使用。 
*  使用该项目时，请在明显的位置保留该系统的版权标识，并不得修改版权信息。


### 版权

*  该系统所属版权归 deatil(https://github.com/deatil) 所有。
