<?php

namespace Larke\Admin\Repository;

use Arr;

use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Model\AuthGroup as AuthGroupModel;
use Larke\Admin\Model\AuthRule as AuthRuleModel;

/*
 * 管理员仓库
 *
 * @create 2020-10-27
 * @author deatil
 */
class Admin
{
    /*
     * 获取 Groups
     */
    public static function getGroups($adminid = null)
    {
        if (empty($adminid)) {
            return [];
        }
        
        $adminInfo = AdminModel::where('id', $adminid)
            ->with('groups')
            ->first();
        if (empty($adminInfo)) {
            return [];
        }
        
        $groups = collect($adminInfo['groups'])->map(function($data) {
            return [
                'id' => $data['id'],
                'parentid' => $data['parentid'],
                'title' => $data['title'],
                'description' => $data['description'],
            ];
        });
        
        return $groups;
    }
    
    /*
     * 获取 groupids
     */
    public static function getGroupids($adminid = null)
    {
        $groupids = self::getGroups($adminid);
        return collect($groupids)->pluck('id');
    }
    
    /*
     * 获取 ChildrenGroups
     */
    public static function getGroupChildren($adminid = null)
    {
        if (empty($adminid)) {
            return [];
        }
        
        $list = AuthGroupModel::with('groupAccess')
            ->whereHas('groupAccess', function($query) use($adminid) {
                $query->where('admin_id', $adminid);
            })
            ->where('status', 1)
            ->orderBy('listorder', 'ASC')
            ->orderBy('create_time', 'ASC')
            ->get()
            ->toArray();
        
        return $list;
    }
    
    /*
     * 获取 ChildrenGroupids
     */
    public static function getGroupChildrenIds($adminid = null)
    {
        $groupids = self::getGroupChildren($adminid);
        return collect($groupids)->pluck('id');
    }
    
    /*
     * 获取 rules
     */
    public static function getRules($groupids = [])
    {
        if (empty($groupids)) {
            return [];
        }
        
        $groupRules = AuthGroupModel::with(['rules' => function($query) {
            $query->select([
                'id', 
                'parentid', 
                'title', 
                'url',
                'method',
                'description',
            ]);
        }])->whereHas('rules', function($query) {
            $query->where('status', 1);
        })->whereIn('id', $groupids)
            ->get()
            ->toArray();
        
        $rules = collect($groupRules)->filter(function($data) {
            return !empty($data['rules']);
        })->map(function($data) {
            return $data['rules'];
        });
        
        $rules = Arr::collapse($rules);
        $list = collect($rules)->map(function($data) {
            unset($data['pivot']);
            return $data;
        })->toArray();
        
        return $list;
    }
    
    /*
     * 获取 ruleids
     */
    public static function getRuleids($groupids = [])
    {
        $list = self::getRules($groupids);
        return collect($list)->pluck('id');
    }
    
    /*
     * 获取 ChildrenRules
     */
    public static function getRuleChildren($groupids = null)
    {
        if (empty($groupids)) {
            return [];
        }
        
        $list = AuthRuleModel::with('ruleAccess')
            ->whereHas('ruleAccess', function($query) use($groupids) {
                $query->whereIn('group_id', $groupids);
            })
            ->where('status', 1)
            ->orderBy('listorder', 'ASC')
            ->orderBy('create_time', 'ASC')
            ->get()
            ->toArray();
        
        return $list;
    }
    
    /*
     * 获取 ChildrenRuleids
     */
    public static function getRuleChildrenIds($groupids = null)
    {
        $list = self::getRuleChildren($groupids);
        return collect($list)->pluck('id');
    }

}
