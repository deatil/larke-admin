<?php

declare (strict_types = 1);

namespace Larke\Admin\Repository;

use Arr;

use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Model\AuthRule as AuthRuleModel;

/*
 * 管理员
 *
 * @create 2020-10-27
 * @author deatil
 */
class Admin
{
    /*
     * 获取 Groups
     */
    public static function getGroups(?mixed $adminid = null)
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
    public static function getGroupids(?mixed $adminid = null)
    {
        $groupids = self::getGroups($adminid);
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
        
        $rules = AuthRuleModel::with('ruleAccess')
            ->select([
                'id', 
                'parentid', 
                'title', 
                'url',
                'method',
                'slug',
                'description',
            ])
            ->whereHas('ruleAccess', function($query) use($groupids) {
                $query->whereIn('group_id', $groupids);
            })
            ->where('status', 1)
            ->orderBy('listorder', 'DESC')
            ->get()
            ->toArray();
        
        $list = collect($rules)->map(function($data) {
            unset($data['rule_access']);
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
     * 获取 AllRules
     */
    public static function getAllRules()
    {
        $rules = AuthRuleModel::select([
            'id', 
            'parentid', 
            'title', 
            'url',
            'method',
            'slug',
            'description',
        ])->where('status', 1)
            ->orderBy('listorder', 'DESC')
            ->orderBy('create_time', 'ASC')
            ->get()
            ->toArray();
        
        return $rules;
    }

}
