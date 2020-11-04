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
        if (empty($ruleid)) {
            return [];
        }
        
        if (is_array($ruleid)) {
            $data = [];
            foreach ($ruleid as $id) {
                $data[] = self::getChildren($id);
            }
            
            $list = Arr::collapse($data);
            
            return $list;
        } else {
            $data = AuthRuleModel::with('children')
                ->where('parentid', $ruleid)
                ->where('status', 1)
                ->orderBy('listorder', 'ASC')
                ->orderBy('create_time', 'ASC')
                ->get()
                ->toArray();
                
            $TreeService = new TreeService();
            $res = $TreeService
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

}
