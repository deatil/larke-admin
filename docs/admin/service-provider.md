## 扩展服务提供者自带方法


### 添加开启扩展后运行前函数

```php
starting(Closure $callback)
```

### 添加开启扩展后运行后函数

```php
started(Closure $callback)
```

### 启动，只有启用后加载

```php
start()
```

### 添加扩展

```php
addExtension(
    string $name = null, 
    string $composerFile = '', 
    string $icon = '', 
    array  $config = []
)
```

### 添加路由

```php
addRoute($callback, $config = [])
```

### 添加登陆过滤

```php
addAuthenticateExcepts(array $excepts)
```

### 添加权限过滤

```php
addPermissionExcepts(array $excepts)
```

### 注册新命名空间

```php
registerNamespace($prefix, $paths = [])
```
