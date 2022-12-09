## app-admin 生成文档

带选择 `--force` 将会覆盖旧文件或者目录


### 初始化 app-admin 目录

运行命令后将会生成目录 `app/Admin`，生成文档后请查看文档 `app/Admin/README.md`

```php
php artisan larke-admin:app-admin create_app_admin
php artisan larke-admin:app-admin create_app_admin --force
```


### 创建控制器

运行命令后将会在目录 `app/Admin/Http/Controllers` 生成代码文件 `NewsContentController.php`

```php
php artisan larke-admin:app-admin create_controller --name=NewsContent
php artisan larke-admin:app-admin create_controller --name=NewsContent --force
```


### 创建模型

运行命令后将会在目录 `app/Admin/Models` 生成代码文件 `NewsContent.php`

```php
php artisan larke-admin:app-admin create_model --name=NewsContent
php artisan larke-admin:app-admin create_model --name=NewsContent --force
```
