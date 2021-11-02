<?php

declare (strict_types = 1);

namespace Larke\Admin\Extension;

use Illuminate\Support\Arr;

use Larke\Admin\Support\Tree;
use Larke\Admin\Model\AuthRule as AuthRuleModel;

/*
 * 规则
 *
 * @create 2021-1-2
 * @author deatil
 */
class Rule
{
    /**
     * 创建
     *
     * @param array $data 
     * @param int|string $parentId 
     *
     * @return array
     */
    public static function create($data = [], $parentId = 0) 
    {
        if (empty($data)) {
            return false;
        }
        
        $lastOrder = AuthRuleModel::max('listorder');
        
        $rule = AuthRuleModel::create([
            'parentid' => $parentId,
            'listorder' => $lastOrder + 1,
            'title' => Arr::get($data, 'title'),
            'url' => Arr::get($data, 'url'),
            'method' => Arr::get($data, 'method'),
            'slug' => Arr::get($data, 'slug'),
            'description' => Arr::get($data, 'description', ''),
        ]);
        
        $children = Arr::get($data, 'children', []);
        foreach ($children as $child) {
            static::create($child, $rule->id);
        }

        return $rule;
    }

    /**
     * 删除
     *
     * @param string $slug 规则slug
     *
     * @return boolean
     */
    public static function delete($slug)
    {
        $ids = self::getAuthRuleIdsBySlug($slug);
        if (!$ids) {
            return false;
        }
        
        collect($ids)->each(function($id) {
            AuthRuleModel::where('id', '=', $id)
                ->first()
                ->delete();
        });
        
        return true;
    }

    /**
     * 启用
     *
     * @param string $slug
     *
     * @return boolean
     */
    public static function enable($slug)
    {
        $ids = self::getAuthRuleIdsBySlug($slug);
        if (! $ids) {
            return false;
        }
        
        collect($ids)->each(function($id) {
            AuthRuleModel::where('id', '=', $id)
                ->first()
                ->enable();
        });
        
        return true;
    }

    /**
     * 禁用
     *
     * @param string $slug
     *
     * @return boolean
     */
    public static function disable($slug)
    {
        $ids = self::getAuthRuleIdsBySlug($slug);
        if (!$ids) {
            return false;
        }
        
        collect($ids)->each(function($id) {
            AuthRuleModel::where('id', '=', $id)
                ->first()
                ->disable();
        });
        
        return true;
    }

    /**
     * 导出指定slug的规则
     *
     * @param string $slug
     *
     * @return array
     */
    public static function export($slug)
    {
        $ids = self::getAuthRuleIdsBySlug($slug);
        if (!$ids) {
            return [];
        }
        
        $ruleList = [];
        $rule = AuthRuleModel::where('slug', '=', $slug)
            ->first()
            ->toArray();

        if ($rule) {
            $ruleList = AuthRuleModel::orderBy('listorder', 'ASC')
                ->where('id', 'in', $ids)
                ->get()
                ->toArray();
                
            $ruleList = Tree::create()
                ->withConfig('buildChildKey', 'children')
                ->withData($ruleList)
                ->build($rule['id']);
        }
        
        return $ruleList;
    }

    /**
     * 根据slug获取规则IDS
     *
     * @param string $slug
     *
     * @return array
     */
    public static function getAuthRuleIdsBySlug($slug)
    {
        $ids = [];
        $rule = AuthRuleModel::where('slug', '=', $slug)
            ->get()
            ->toArray();
            
        $idsList = collect($rule)->map(function($rule) {
            $ids = [];
            if ($rule) {
                $ruleList = AuthRuleModel::orderBy('listorder', 'ASC')
                    ->select(['id', 'parentid', 'slug'])
                    ->get()
                    ->toArray();
                $ids = Tree::create()
                    ->getListChildrenId($ruleList, $rule['id']);
                $ids[] = $rule['id'];
            }
            
            return $ids;
        });
        $ids = collect($idsList)->collapse();
        
        return $ids;
    }

}
