<?php

use Illuminate\Support\Facades\Route;
use Larke\Admin\Controller\{
    AuthRule,
    AuthGroup,
    Passport,
    Profile,
    Attachment,
    Upload,
    Admin,
    Config,
    Extension,
    System,
    Menu,
};

Route::group([
    'domain'     => config('larkeadmin.route.domain'),
    'prefix'     => config('larkeadmin.route.prefix'),
    'middleware' => config('larkeadmin.route.middleware'),
    'as'         => config('larkeadmin.route.as'),
], function ($router) {
    $router->group([
        'middleware' => config('larkeadmin.route.admin_middleware'),
    ], function ($router) {
        // 权限菜单
        $router->get('/auth/rule', [AuthRule::class, 'index'])->name('auth-rule.index');
        $router->get('/auth/rule/tree', [AuthRule::class, 'indexTree'])->name('auth-rule.index-tree');
        $router->get('/auth/rule/children', [AuthRule::class, 'indexChildren'])->name('auth-rule.index-children');
        $router->get('/auth/rule/{id}', [AuthRule::class, 'detail'])->name('auth-rule.detail')->where('id', '[A-Za-z0-9\-]+');
        $router->post('/auth/rule', [AuthRule::class, 'create'])->name('auth-rule.create');
        $router->put('/auth/rule/{id}', [AuthRule::class, 'update'])->name('auth-rule.update')->where('id', '[A-Za-z0-9\-]+');
        $router->delete('/auth/rule/clear', [AuthRule::class, 'clear'])->name('auth-rule.clear');
        $router->delete('/auth/rule/{id}', [AuthRule::class, 'delete'])->name('auth-rule.delete')->where('id', '[A-Za-z0-9\-]+');
        $router->patch('/auth/rule/{id}/sort', [AuthRule::class, 'listorder'])->name('auth-rule.listorder')->where('id', '[A-Za-z0-9\-]+');
        $router->patch('/auth/rule/{id}/enable', [AuthRule::class, 'enable'])->name('auth-rule.enable')->where('id', '[A-Za-z0-9\-]+');
        $router->patch('/auth/rule/{id}/disable', [AuthRule::class, 'disable'])->name('auth-rule.disable')->where('id', '[A-Za-z0-9\-]+');
        
        // 管理分组
        $router->get('/auth/group', [AuthGroup::class, 'index'])->name('auth-group.index');
        $router->get('/auth/group/tree', [AuthGroup::class, 'indexTree'])->name('auth-group.index-tree');
        $router->get('/auth/group/children', [AuthGroup::class, 'indexChildren'])->name('auth-group.index-children');
        $router->get('/auth/group/{id}', [AuthGroup::class, 'detail'])->name('auth-group.detail')->where('id', '[A-Za-z0-9\-]+');
        $router->post('/auth/group', [AuthGroup::class, 'create'])->name('auth-group.create');
        $router->put('/auth/group/{id}', [AuthGroup::class, 'update'])->name('auth-group.update')->where('id', '[A-Za-z0-9\-]+');
        $router->delete('/auth/group/{id}', [AuthGroup::class, 'delete'])->name('auth-group.delete')->where('id', '[A-Za-z0-9\-]+');
        $router->patch('/auth/group/{id}/sort', [AuthGroup::class, 'listorder'])->name('auth-group.listorder')->where('id', '[A-Za-z0-9\-]+');
        $router->patch('/auth/group/{id}/enable', [AuthGroup::class, 'enable'])->name('auth-group.enable')->where('id', '[A-Za-z0-9\-]+');
        $router->patch('/auth/group/{id}/disable', [AuthGroup::class, 'disable'])->name('auth-group.disable')->where('id', '[A-Za-z0-9\-]+');
        $router->patch('/auth/group/{id}/access', [AuthGroup::class, 'access'])->name('auth-group.access')->where('id', '[A-Za-z0-9\-]+');
    });
    
    // 登陆
    $router->get('/passport/captcha', [Passport::class, 'captcha'])->name('passport.captcha');
    $router->get('/passport/passkey', [Passport::class, 'passkey'])->name('passport.passkey');
    $router->post('/passport/login', [Passport::class, 'login'])->name('passport.login');
    $router->put('/passport/refresh-token', [Passport::class, 'refreshToken'])->name('passport.refresh-token');
    $router->delete('/passport/logout', [Passport::class, 'logout'])->name('passport.logout');
    
    // 个人信息
    $router->get('/profile', [Profile::class, 'index'])->name('profile');
    $router->put('/profile/update', [Profile::class, 'update'])->name('profile.update');
    $router->patch('/profile/avatar', [Profile::class, 'updateAvatar'])->name('profile.avatar');
    $router->patch('/profile/password', [Profile::class, 'updatePasssword'])->name('profile.password');
    $router->get('/profile/rules', [Profile::class, 'rules'])->name('profile.rules');
    $router->get('/profile/roles', [Profile::class, 'roles'])->name('profile.roles');
    
    // 附件
    $router->get('/attachment', [Attachment::class, 'index'])->name('attachment.index');
    $router->get('/attachment/{id}', [Attachment::class, 'detail'])->name('attachment.detail')->where('id', '[A-Za-z0-9\-]+');
    $router->patch('/attachment/{id}/enable', [Attachment::class, 'enable'])->name('attachment.enable')->where('id', '[A-Za-z0-9\-]+');
    $router->patch('/attachment/{id}/disable', [Attachment::class, 'disable'])->name('attachment.disable')->where('id', '[A-Za-z0-9\-]+');
    $router->delete('/attachment/{id}', [Attachment::class, 'delete'])->name('attachment.delete')->where('id', '[A-Za-z0-9\-]+');
    $router->get('/attachment/downcode/{id}', [Attachment::class, 'downloadCode'])->name('attachment.download-code')->where('id', '[A-Za-z0-9\-]+');
    $router->get('/attachment/download/{code}', [Attachment::class, 'download'])->name('attachment.download')->where('code', '[A-Za-z0-9]+');
    
    // 上传
    $router->post('/upload/file', [Upload::class, 'file'])->name('upload.file');
    
    // 管理员
    $router->get('/admin', [Admin::class, 'index'])->name('admin.index');
    $router->get('/admin/groups', [Admin::class, 'groups'])->name('admin.groups');
    $router->get('/admin/{id}/rules', [Admin::class, 'rules'])->name('admin.rules')->where('id', '[A-Za-z0-9\-]+');
    $router->get('/admin/{id}', [Admin::class, 'detail'])->name('admin.detail')->where('id', '[A-Za-z0-9\-]+');
    $router->post('/admin', [Admin::class, 'create'])->name('admin.create');
    $router->put('/admin/reset-permission', [Admin::class, 'ResetPermission'])->name('admin.reset-permission');
    $router->put('/admin/{id}', [Admin::class, 'update'])->name('admin.update')->where('id', '[A-Za-z0-9\-]+');
    $router->delete('/admin/{id}', [Admin::class, 'delete'])->name('admin.delete')->where('id', '[A-Za-z0-9\-]+');
    $router->patch('/admin/{id}/enable', [Admin::class, 'enable'])->name('admin.enable')->where('id', '[A-Za-z0-9\-]+');
    $router->patch('/admin/{id}/disable', [Admin::class, 'disable'])->name('admin.disable')->where('id', '[A-Za-z0-9\-]+');
    $router->patch('/admin/{id}/avatar', [Admin::class, 'updateAvatar'])->name('admin.avatar')->where('id', '[A-Za-z0-9\-]+');
    $router->patch('/admin/{id}/password', [Admin::class, 'updatePasssword'])->name('admin.password')->where('id', '[A-Za-z0-9\-]+');
    $router->patch('/admin/{id}/access', [Admin::class, 'access'])->name('admin.access')->where('id', '[A-Za-z0-9\-]+');
    $router->delete('/admin/logout/{refreshToken}', [Admin::class, 'logout'])->name('admin.logout');
    
    // 配置
    $router->get('/config/settings', [Config::class, 'settings'])->name('config.settings');
    $router->get('/config/list', [Config::class, 'lists'])->name('config.lists');
    $router->get('/config', [Config::class, 'index'])->name('config.index');
    $router->get('/config/{id}', [Config::class, 'detail'])->name('config.detail')->where('id', '[A-Za-z0-9\-]+');
    $router->post('/config', [Config::class, 'create'])->name('config.create');
    $router->put('/config/setting', [Config::class, 'setting'])->name('config.setting');
    $router->put('/config/{id}', [Config::class, 'update'])->name('config.update')->where('id', '[A-Za-z0-9\-]+');
    $router->delete('/config/{id}', [Config::class, 'delete'])->name('config.delete')->where('id', '[A-Za-z0-9\-]+');
    $router->patch('/config/{id}/sort', [Config::class, 'listorder'])->name('config.listorder')->where('id', '[A-Za-z0-9\-]+');
    $router->patch('/config/{id}/enable', [Config::class, 'enable'])->name('config.enable')->where('id', '[A-Za-z0-9\-]+');
    $router->patch('/config/{id}/disable', [Config::class, 'disable'])->name('config.disable')->where('id', '[A-Za-z0-9\-]+');
    
    // 扩展
    $router->get('/extension/index', [Extension::class, 'index'])->name('extension.index');
    $router->get('/extension/local', [Extension::class, 'local'])->name('extension.local');
    $router->put('/extension/refresh', [Extension::class, 'refreshLocal'])->name('extension.refresh');
    $router->get('/extension/command/{name}', [Extension::class, 'command'])->name('extension.command')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->post('/extension/install/{name}', [Extension::class, 'install'])->name('extension.install')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->delete('/extension/uninstall/{name}', [Extension::class, 'uninstall'])->name('extension.uninstall')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->put('/extension/upgrade/{name}', [Extension::class, 'upgrade'])->name('extension.upgrade')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->put('/extension/{name}/repository-register', [Extension::class, 'repositoryRegister'])->name('extension.repository-register')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->put('/extension/{name}/repository-remove', [Extension::class, 'repositoryRemove'])->name('extension.repository-remove')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->put('/extension/{name}/config', [Extension::class, 'config'])->name('extension.config')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->patch('/extension/{name}/enable', [Extension::class, 'enable'])->name('extension.enable')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->patch('/extension/{name}/disable', [Extension::class, 'disable'])->name('extension.disable')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->patch('/extension/{name}/sort', [Extension::class, 'listorder'])->name('extension.listorder')->where('name', '[A-Za-z0-9\-\_\.\/]+');
    $router->post('/extension/upload', [Extension::class, 'upload'])->name('extension.upload');
    
    // 系统
    $router->get('/system/info', [System::class, 'info'])->name('system.info');
    $router->get('/system/lang', [System::class, 'lang'])->name('system.lang');
    $router->patch('/system/lang/{locale}', [System::class, 'setLang'])->name('system.set-lang')->where('locale', '[A-Za-z0-9\-\_]+');
    $router->post('/system/cache', [System::class, 'cache'])->name('system.cache');
    $router->post('/system/clear-cache', [System::class, 'clearCache'])->name('system.clear-cache');
    $router->get('/system/menus', [System::class, 'menus'])->name('system.menus');
    $router->get('/system/menus-tree', [System::class, 'menusTree'])->name('system.menus-tree');

    // 菜单
    $router->get('/menu', [Menu::class, 'index'])->name('menu.index');
    $router->get('/menu/tree', [Menu::class, 'indexTree'])->name('menu.index-tree');
    $router->get('/menu/children', [Menu::class, 'indexChildren'])->name('menu.index-children');
    $router->post('/menu', [Menu::class, 'create'])->name('menu.create');
    $router->put('/menu/{id}', [Menu::class, 'update'])->name('menu.update');
    $router->delete('/menu/{id}', [Menu::class, 'delete'])->name('menu.delete');
    $router->get('/menu/json', [Menu::class, 'getJson'])->name('menu.json');
    $router->put('/menu/save-json', [Menu::class, 'saveJson'])->name('menu.save-json');

});
