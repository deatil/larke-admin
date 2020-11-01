<?php

namespace Larke\Admin\Auth;

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
     * id
     */
    protected $id = null;
    
    /*
     * data
     */
    protected $data = [];
    
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
        if (empty($this->data)) {
            return [];
        }
        
        $data = collect($this->data)->only([
            "id",
            "name",
            "nickname",
            "avatar",
            "email",
            "last_active",
            "last_ip",
            "groups",
        ]);
        
        $data['groups'] = collect($data['groups'])->map(function($data) {
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
     * 是否为管理员
     */
    public function isAdministrator()
    {
        if (empty($this->data)) {
            return false;
        }
        
        if (!isset($this->data['is_root']) 
            || $this->data['is_root'] != 1
        ) {
            return false;
        }
        
        return ($this->id == config('larke.auth.admin_id'));
    }
    
    /*
     * 获取 groups
     */
    public function getGroups()
    {
        $data = $this->getData();
        return $data['groups'] ?? [];
    }
    
    /*
     * 获取 groupids
     */
    public function getGroupids()
    {
        $groups = $this->getGroups();
        return collect($groups)->pluck('id')->toArray();
    }
    
    /*
     * 获取 ChildrenRules
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
     * 获取 ChildrenRuleids
     */
    public function getGroupChildrenIds()
    {
        $list = $this->getGroupChildren();
        return collect($list)->pluck('id')->toArray();
    }
    
    /*
     * 获取 rules
     */
    public function getRules()
    {
        if ($this->isAdministrator()) {
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
        $ruleids = $this->getRules();
        return collect($ruleids)->pluck('id');
    }

}
