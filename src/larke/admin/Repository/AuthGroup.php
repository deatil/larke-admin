<?php

namespace Larke\Admin\Repository;

use Arr;

use Larke\Admin\Service\Tree as TreeService;
use Larke\Admin\Model\AuthGroup as AuthGroupModel;

/*
 * AuthGroup
 *
 * @create 2020-10-27
 * @author deatil
 */
class AuthGroup
{
    
    /*
     * 获取 Children
     */
    public static function getChildren($groupid = null)
    {
        if (empty($groupid)) {
            return [];
        }
        
        if (is_array($groupid)) {
            $data = [];
            foreach ($groupid as $id) {
                $data[] = self::getChildren($id);
            }
            
            $list = Arr::collapse($data);
            
            return $list;
        } else {
            $data = AuthGroupModel::with('children')
                ->where('parentid', $groupid)
                ->where('status', 1)
                ->orderBy('listorder', 'ASC')
                ->orderBy('create_time', 'ASC')
                ->get()
                ->toArray();
                
            $TreeService = new TreeService();
            $res = $TreeService
                ->withData($data)
                ->build($groupid);
            
            $list = $TreeService->buildFormatList($res, $groupid);
            return $list;
        }
    }
    
    /*
     * 获取 ChildrenIds
     */
    public static function getChildrenIds($groupid = null)
    {
        $list = self::getChildren($groupid);
        return collect($list)->pluck('id')->toArray();
    }

}
