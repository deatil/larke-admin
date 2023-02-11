# larke-admin通用后台管理系统

larke-admin 是一套使用 Laravel、JWT 和 RBAC鉴权的通用后台管理系统


## 项目介绍

*  `larke-admin` 是基于 `laravel` 框架的后台管理系统，完全api接口化，适用于前后端分离的项目
*  基于 `JWT` 的用户登录态管理
*  权限判断基于 `php-casbin` 的 `RBAC` 授权
*  本项目为 `后台api服务`，`后台前端页面` 可查看 [Larke Admin Frontend](https://github.com/deatil/larke-admin-frontend) 项目


## 环境要求

 - PHP >= 8.0.2
 - Laravel >= 9.0.0
 - Fileinfo PHP Extension


## 截图预览

<table>
    <tr>
        <td width="50%">
            <center>
                <img alt="login" src="https://user-images.githubusercontent.com/24578855/103483910-8cec8780-4e25-11eb-93c5-ea7ce7a09b60.png" />
            </center>
        </td>
        <td width="50%">
            <center>
                <img alt="index" src="https://user-images.githubusercontent.com/24578855/105568367-cacd3380-5d73-11eb-98ab-55701d0068ed.png" />
            </center>
        </td>
    </tr>
    <tr>
        <td width="50%">
            <center>
                <img alt="admin" src="https://user-images.githubusercontent.com/24578855/101988564-6bd8c100-3cd5-11eb-8524-21151ba3b404.png" />
            </center>
        </td>
        <td width="50%">
            <center>
                <img alt="admin-access" src="https://user-images.githubusercontent.com/24578855/103433753-db393500-4c31-11eb-8d8a-b40dfa0db84e.png" />
            </center>
        </td>
    </tr>
    <tr>
        <td width="50%">
            <center>
                <img alt="attach" src="https://user-images.githubusercontent.com/24578855/101988566-6da28480-3cd5-11eb-9532-69d88b2f598d.png" />
            </center>
        </td>
        <td width="50%">
            <center>
                <img alt="config" src="https://user-images.githubusercontent.com/24578855/101988567-6e3b1b00-3cd5-11eb-8799-66e8ebec6020.png" />
            </center>
        </td>
    </tr>
    <tr>
        <td width="50%">
            <center>
                <img alt="menus" src="https://user-images.githubusercontent.com/24578855/101988573-71cea200-3cd5-11eb-8e8b-e80ab319b216.png" />
            </center>
        </td>
        <td width="50%">
            <center>
                <img alt="rule2" src="https://user-images.githubusercontent.com/24578855/102609155-f9992e00-4165-11eb-93ad-82275af134ab.png" />
            </center>
        </td>
    </tr>
</table>

更多截图 
[Larke Admin 后台截图](https://github.com/deatil/larke-admin/issues/1)


## 安装步骤

1. 首先安装 `laravel` 框架，并确认连接数据库的配置没有问题，开始执行以下命令

```php
composer require lake/larke-admin
```

2. 然后运行下面的命令，推送配置文件

```php
php artisan vendor:publish --tag=larke-admin-config
```

运行完命令后

你需要复制 `config/larkeadmin.php.larke` 重命名为 `config/larkeadmin.php`，

复制 `config/larkeauth.php.larke` 重命名为 `config/larkeauth.php`，

复制 `larkeauth-rbac-model.conf.larke` 重命名为 `larkeauth-rbac-model.conf`

如果文件已存在，请根据情况复制内容到对应配置文件

3. 最后运行下面的命令安装完成系统

```php
php artisan larke-admin:install
```

4. 你可能第一次安装需要运行以下命令导入路由权限规则

```php
php artisan larke-admin:import-route
```

5. 后台登录账号及密码：`admin` / `123456`


## 扩展推荐

| 名称 | 描述 |
| --- | --- |
| [demo](https://github.com/deatil/larke-admin-demo) | 扩展示例 |
| [操作日志](https://github.com/deatil/larke-operation-log) | 记录 admin 系统的相关操作日志 |
| [签名证书](https://github.com/deatil/larke-admin-signcert) | 生成RSA,EDDSA,ECDSA等非对称签名证书 |
| [日志查看器](https://github.com/deatil/larke-admin-logviewer) | laravel日志查看扩展 |

注：扩展目录默认为 `/extension` 目录


## 特别鸣谢

感谢以下的项目,排名不分先后

 - laravel/framework

 - [lake/larke-jwt](https://github.com/deatil/larke-jwt) (代码修改自 lcobucci/jwt)

 - casbin/casbin

 - composer/semver

 - phpseclib/phpseclib
 
 - PclZip


## 开源协议

*  `larke-admin` 遵循 `Apache2` 开源协议发布，在保留本系统版权的情况下提供个人及商业免费使用。 


## 版权

*  该系统所属版权归 deatil(https://github.com/deatil) 所有。
