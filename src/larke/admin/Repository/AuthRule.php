<?php

namespace Larke\Admin\Repository;

use Arr;

use Larke\Admin\Service\Tree as TreeService;
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
     * 获取 Children
     */
    public static function getChildren($ruleid = null)
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
                ->orderBy('listorder', 'ASC')
                ->orderBy('create_time', 'ASC')
                ->get()
                ->toArray();
                
            $TreeService = new TreeService();
            $res = $TreeService
                ->withConfig('buildChildKey', 'children')
                ->withData($data)
                ->build($ruleid);
            
            $list = $TreeService->buildFormatList($res, $ruleid);
            return $list;
        }
    }
    
    /*
     * 获取 ChildrenIds
     */
    public static function getChildrenIds($ruleid = null)
    {
        $list = self::getChildren($ruleid);
        return collect($list)->pluck('id')->toArray();
    }
    
    /*
     * 获取 Children
     */
    public static function getChildrenFromData($data = [], $parentid = '')
    {
        $TreeService = new TreeService();
        $res = $TreeService
            ->withConfig('buildChildKey', 'children')
            ->withData($data)
            ->build($parentid);
        
        $list = $TreeService->buildFormatList($res, $parentid);
        
        return $list;
    }
    
    /*
     * 获取 ChildrenIds
     */
    public static function getChildrenIdsFromData($data = [], $parentid = '')
    {
        $list = self::getChildrenFromData($data, $parentid);
        
        return collect($list)->pluck('id')->toArray();
    }

}
