<?php

declare (strict_types = 1);

namespace Larke\Admin\Repository;

use Arr;

use Larke\Admin\Support\Tree;
use Larke\Admin\Model\AuthGroup as AuthGroupModel;

/**
 * AuthGroup
 *
 * @create 2020-10-27
 * @author deatil
 */
class AuthGroup
{
    /**
     * 获取全部用户组
     */
    public static function getAllGroup()
    {
        $data = AuthGroupModel::query()
            ->where('status', 1)
            ->orderBy('listorder', 'ASC')
            ->orderBy('create_time', 'ASC')
            ->get()
            ->toArray();
        
        return $data;
    }
    
    /**
     * 获取 Children
     */
    public static function getChildren($groupid = null)
    {
        if (is_array($groupid)) {
            $data = [];
            foreach ($groupid as $id) {
                $data[] = self::getChildren($id);
            }
            
            $list = Arr::collapse($data);
            
            return $list;
        } else {
            $wheres = [
                ['status', 1],
            ];
            if (! empty($groupid)) {
                $wheres[] = ['parentid', $groupid];
            }
            
            $data = AuthGroupModel::with('children')
                ->wheres($wheres)
                ->orderBy('listorder', 'ASC')
                ->orderBy('create_time', 'ASC')
                ->get()
                ->toArray();
                
            $Tree = new Tree();
            $res = $Tree
                ->withConfig('buildChildKey', 'children')
                ->withData($data)
                ->build($groupid);
            
            $list = $Tree->buildFormatList($res, $groupid);
            return $list;
        }
    }
    
    /**
     * 获取 ChildrenIds
     */
    public static function getChildrenIds($groupid = null)
    {
        $list = self::getChildren($groupid);
        return collect($list)->pluck('id')->toArray();
    }
    
    /**
     * 获取 Children
     */
    public static function getChildrenFromData($data = [], $parentid = '')
    {
        $Tree = new Tree();
        $res = $Tree
            ->withConfig('buildChildKey', 'children')
            ->withData($data)
            ->build($parentid);
        
        $list = $Tree->buildFormatList($res, $parentid);
        
        return $list;
    }
    
    /**
     * 获取 ChildrenIds
     */
    public static function getChildrenIdsFromData($data = [], $parentid = '')
    {
        $list = self::getChildrenFromData($data, $parentid);
        
        return collect($list)->pluck('id')->toArray();
    }

}
