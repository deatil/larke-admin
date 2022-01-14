<?php

declare (strict_types = 1);

namespace Larke\Admin\Auth;

use Illuminate\Support\Arr;

use Larke\Admin\Auth\Permission as AuthPermission;
use Larke\Admin\Repository\Admin as AdminRepository;
use Larke\Admin\Repository\AuthGroup as AuthGroupRepository;

/*
 * 管理员信息
 *
 * @create 2020-10-26
 * @author deatil
 */
class Admin
{
    /*
     * 鉴权Token
     */
    protected $accessToken = null;
    
    /*
     * id
     */
    protected $id = null;
    
    /*
     * data
     */
    protected $data = [];
    
    /*
     * 设置 accessToken
     */
    public function withAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        
        return $this;
    }
    
    /*
     * 获取 accessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
    
    /*
     * 设置 id
     */
    public function withId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /*
     * 获取 id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /*
     * 设置 data
     */
    public function withData($data)
    {
        $this->data = $data;
        
        return $this;
    }
    
    /*
     * 获取 data
     */
    public function getData()
    {
        return $this->data;
    }
    
    /*
     * 获取个人信息
     */
    public function getProfile()
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
        
        return $data;
    }

    /**
     * 是否为超级管理员
     */
    public function isSuperAdministrator()
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
    public function isActive()
    {
        if ($this->isSuperAdministrator()) {
            return true;
        }
        
        return ($this->data['status'] == 1);
    }

    /**
     * 是否用户分组启用
     */
    public function isGroupActive()
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
    public function isGuest()
    {
        return !empty($this->id) ? true : false;
    }

    /**
     * 判断是否有权限
     */
    public function hasAccess($slug, $method = 'GET')
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
    public function getGroups()
    {
        $data = $this->getProfile();
        return Arr::get($data, 'groups', []);
    }
    
    /*
     * 获取用户组ID列表
     */
    public function getGroupids()
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
    public function getGroupChildren()
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
        
        return $list;
    }
    
    /*
     * 获取 GroupChildrenIds
     */
    public function getGroupChildrenIds()
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
    public function getRules()
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
    public function getRuleids()
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
    public function getRuleSlugs()
    {
        $rules = $this->getRules();
        return collect($rules)
            ->sortBy('slug')
            ->pluck('slug')
            ->toArray();
    }

}
