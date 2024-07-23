<?php

declare (strict_types = 1);

namespace Larke\Admin\Repository;

use Arr;

use Larke\Admin\Support\Tree;
use Larke\Admin\Model\AuthRule as AuthRuleModel;

/*
 * AuthRule
 *
 * @create 2020-10-27
 * @author deatil
 */
class AuthRule
{
    /*
     * 获取 AllRules
     */
    public static function getAllRules(): array
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

    /*
     * 获取 Children
     */
    public static function getChildren(mixed $ruleid = null): array
    {
        if (is_array($ruleid)) {
            $data = [];
            foreach ($ruleid as $id) {
                $data[] = self::getChildren($id);
            }
            
            $list = Arr::collapse($data);
            
            return $list;
        } else {
            $wheres = [
                ['status', 1],
            ];
            if (! empty($ruleid)) {
                $wheres[] = ['parentid', $ruleid];
            }
        
            $data = AuthRuleModel::with('children')
                ->wheres($wheres)
                ->orderBy('listorder', 'DESC')
                ->orderBy('create_time', 'ASC')
                ->get()
                ->toArray();
                
            $Tree = new Tree();
            $res = $Tree
                ->withConfig('buildChildKey', 'children')
                ->withData($data)
                ->build($ruleid);
            
            $list = $Tree->buildFormatList($res, $ruleid);
            return $list;
        }
    }
    
    /*
     * 获取 ChildrenIds
     */
    public static function getChildrenIds(mixed $ruleid = null): array
    {
        $list = self::getChildren($ruleid);
        return collect($list)->pluck('id')->toArray();
    }
    
    /*
     * 获取 Children
     */
    public static function getChildrenFromData(mixed $data = [], mixed $parentid = ''): array
    {
        $Tree = new Tree();
        $res = $Tree
            ->withConfig('buildChildKey', 'children')
            ->withData((array) $data)
            ->build($parentid);
        
        $list = $Tree->buildFormatList($res, $parentid);
        
        return $list;
    }
    
    /*
     * 获取 ChildrenIds
     */
    public static function getChildrenIdsFromData(mixed $data = [], mixed $parentid = ''): array
    {
        $list = self::getChildrenFromData((array) $data, $parentid);
        
        return collect($list)->pluck('id')->toArray();
    }

}
