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
    public static function getAllGroup(): array
    {
        $data = AuthGroupModel::query()
            ->where('status', 1)
            ->orderBy('listorder', 'DESC')
            ->orderBy('create_time', 'ASC')
            ->get()
            ->toArray();
        
        return $data;
    }
    
    /**
     * 获取 Children
     */
    public static function getChildren(mixed $groupid = null): array
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
                ->orderBy('listorder', 'DESC')
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
    public static function getChildrenIds(mixed $groupid = null): array
    {
        $list = self::getChildren($groupid);
        return collect($list)->pluck('id')->toArray();
    }
    
    /**
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
    
    /**
     * 获取 ChildrenIds
     */
    public static function getChildrenIdsFromData(mixed $data = [], mixed $parentid = ''): array
    {
        $list = self::getChildrenFromData((array) $data, $parentid);
        
        return collect($list)->pluck('id')->toArray();
    }

}
