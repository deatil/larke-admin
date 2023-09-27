## 扩展服务提供者自带方法


### 添加开启扩展后运行前函数

```php
starting(Closure $callback)
```

### 添加开启扩展后运行后函数

```php
started(Closure $callback)
```

### 登陆过滤

```php
authenticateExcepts(array $excepts)
withAuthenticateExcepts(array $excepts = [])
```

### 权限过滤

```php
permissionExcepts(array $excepts)
withPermissionExcepts(array $excepts = [])
```

### 启动，只有启用后加载

```php
start()
```

### 添加扩展

```php
withExtension($pkgName, Info $info = null)
```

### 从 composer.json 添加扩展

```php
withExtensionFromComposer(
    string $name = null, 
    string $composerFile = '', 
    string $icon = '', 
    array  $config = []
)
```

### 添加扩展信息

```php
makeExtensionInfo(
    $name = null, 
    array  $info = [], 
    string $icon = '', 
    array  $config = []
)
```

### 设置命名空间

```php
withNamespace($prefix, $paths = [])
```

### 设置扩展路由

```php
withRoute($callback, $config = [])
```

### 事件，安装后

```php
onInatll(Closure $callback)
```

### 事件，卸载后

```php
onUninstall(Closure $callback)
```

### 事件，更新后

```php
onUpgrade(Closure $callback)
```

### 事件，启用后

```php
onEnable(Closure $callback)
```

### 事件，禁用后

```php
onDisable(Closure $callback)
```
