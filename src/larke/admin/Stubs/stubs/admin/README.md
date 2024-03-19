### 后台路由设置

路由已经有设置的只需要添加新的路由到路由文件(`app\Admin\routes.php`)，
如果没有引入路由，需要添加路由信息到 `App\Providers\AppServiceProvider::boot()` 方法内。

~~~php
// 加载路由
$this->loadRoutesFrom(__DIR__ . '/../Admin/routes.php');
~~~

