<?php

declare (strict_types = 1);

namespace Larke\Admin\Auth;

use Illuminate\Support\Arr;

use Larke\Admin\Facade\Permission as AuthPermission;
use Larke\Admin\Repository\Admin as AdminRepository;
use Larke\Admin\Repository\AuthGroup as AuthGroupRepository;

/**
 * 管理员信息
 *
 * @create 2020-10-26
 * @author deatil
 */
class Admin
{
    /**
     * 鉴权Token
     */
    protected string $accessToken = '';
    
    /**
     * 用户id
     */
    protected string $id = '';
    
    /**
     * 数据
     */
    protected array $data = [];
    
    /**
     * 全部用户组
     */
    protected array $allGroup = [];
    
    /**
     * 设置 accessToken
     */
    public function withAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        
        return $this;
    }
    
    /**
     * 获取 accessToken
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
    
    /**
     * 设置 id
     */
    public function withId($id): self
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * 获取 id
     */
    public function getId(): string
    {
        return $this->id;
    }
    
    /**
     * 设置 data
     */
    public function withData($data): self
    {
        $this->data = $data;
        
        return $this;
    }
    
    /**
     * 获取 data
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * 设置全部用户组
     */
    public function withAllGroup(array $data): self
    {
        $this->allGroup = $data;
        
        return $this;
    }
    
    /**
     * 获取全部用户组
     */
    public function getAllGroup(): array
    {
        if (empty($this->allGroup)) {
            $this->allGroup = AuthGroupRepository::getAllGroup();
        }
        
        return $this->allGroup;
    }
    
    /**
     * 获取个人信息
     */
    public function getProfile(): array
    {
        if (empty($this->data)) {
            return [];
        }
        
        $data = collect($this->data)->only([
            "id",
            "name",
            "email",
            "nickname",
            "avatar",
            "introduce",
            "groups",
            "last_active",
            "last_ip",
        ]);
        
        $data['groups'] = collect($data['groups'])
            ->map(function($data) {
                return [
                    'id' => $data['id'],
                    'parentid' => $data['parentid'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                ];
            });
        
        return $data->toArray();
    }

    /**
     * 是否为超级管理员
     */
    public function isSuperAdministrator(): bool
    {
        if (empty($this->data)) {
            return false;
        }
        
        if (!isset($this->data['is_root']) 
            || $this->data['is_root'] != 1
        ) {
            return false;
        }
        
        return ($this->id == config('larkeadmin.auth.admin_id'));
    }

    /**
     * 是否启用
     */
    public function isActive(): bool
    {
        if ($this->isSuperAdministrator()) {
            return true;
        }
        
        return ($this->data['status'] == 1);
    }

    /**
     * 是否用户分组启用
     */
    public function isGroupActive(): bool
    {
        if ($this->isSuperAdministrator()) {
            return true;
        }
        
        $groups = $this->data['groups'] ?: [];
        return collect($groups)
            ->contains(function ($group) {
                return ($group['status'] == 1);
            });
    }

    /**
     * 是否为匿名用户
     */
    public function isGuest(): bool
    {
        return empty($this->id) ? true : false;
    }

    /**
     * 判断是否有权限
     */
    public function hasAccess(string $slug, string $method = 'GET'): bool
    {
        if ($this->isSuperAdministrator()) {
            return true;
        }
        
        if (! AuthPermission::enforce($this->id, $slug, $method)) {
            return false;
        }
        
        return true;
    }
    
    /*
     * 获取用户组列表
     */
    public function getGroups(): array
    {
        if ($this->isSuperAdministrator()) {
            return $this->getAllGroup();
        }
        
        $data = $this->getProfile();
        return Arr::get($data, 'groups', []);
    }
    
    /*
     * 获取用户组ID列表
     */
    public function getGroupids(): array
    {
        $groups = $this->getGroups();
        return collect($groups)
            ->pluck('id')
            ->unique()
            ->toArray();
    }
    
    /*
     * 获取 GroupChildren
     */
    public function getGroupChildren(): array
    {
        $groupids = $this->getGroupids();
        if (empty($groupids)) {
            return [];
        }
        
        $list = AuthGroupRepository::getChildren($groupids);
        
        $list = collect($list)->map(function($data) {
            return [
                'id' => $data['id'],
                'parentid' => $data['parentid'],
                'title' => $data['title'],
                'description' => $data['description'],
            ];
        });
        
        return $list->toArray();
    }
    
    /*
     * 获取 GroupChildrenIds
     */
    public function getGroupChildrenIds(): array
    {
        $list = $this->getGroupChildren();
        return collect($list)
            ->pluck('id')
            ->unique()
            ->toArray();
    }
    
    /*
     * 获取 rules
     */
    public function getRules(): array
    {
        if ($this->isSuperAdministrator()) {
            $rules = AdminRepository::getAllRules();
        } else {
            $groupids = $this->getGroupids();
            if (empty($groupids)) {
                return [];
            }
            
            $rules = AdminRepository::getRules($groupids);
        }
        
        return $rules;
    }
    
    /*
     * 获取 ruleids
     */
    public function getRuleids(): array
    {
        $rules = $this->getRules();
        
        return collect($rules)
            ->sortBy('id')
            ->pluck('id')
            ->unique()
            ->toArray();
    }
    
    /*
     * 获取 slugs
     */
    public function getRuleSlugs(): array
    {
        $rules = $this->getRules();
        return collect($rules)
            ->sortBy('slug')
            ->pluck('slug')
            ->toArray();
    }

}
