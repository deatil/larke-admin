<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Support\Tree;
use Larke\Admin\Annotation\RouteRule;
use Larke\Admin\Http\Controller as BaseController;
use Larke\Admin\Service\Menu as MenuModel;

/**
 * 菜单管理
 *
 * @create 2022-8-26
 * @author deatil
 */
#[RouteRule(
    title: "菜单管理", 
    desc:  "菜单管理",
    order: 130,
    auth:  true,
    slug:  "{prefix}menu"
)]
class Menu extends BaseController
{
    /**
     * 列表
     *
     * @param  Request  $request
     * @param  MenuModel $menuModel
     * @return Response
     */
    #[RouteRule(
        title: "菜单列表", 
        desc:  "菜单列表管理",
        order: 100,
        auth:  true
    )]
    public function index(Request $request, MenuModel $menuModel)
    {
        $list = $menuModel->getList();
        
        return $this->success(__('larke-admin::common.get_success'), [
            'list' => $list,
        ]);
    }
    
    /**
     * 菜单树
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "菜单树列表", 
        desc:  "菜单树列表管理",
        order: 99,
        auth:  true
    )]
    public function indexTree(Request $request, MenuModel $menuModel)
    {
        $result = $menuModel->getList();
        
        $result = collect($result)
            ->sortByDesc('sort')
            ->toArray();
        
        $Tree = new Tree();
        $list = $Tree
            ->withConfig('parentidKey', 'pid')
            ->withConfig('buildChildKey', 'children')
            ->withData($result)
            ->build(0);
        
        return $this->success(__('larke-admin::common.get_success'), [
            'list' => $list,
        ]);
    }
    
    /**
     * 菜单子列表
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "菜单子列表", 
        desc:  "菜单子列表管理",
        order: 98,
        auth:  true
    )]
    public function indexChildren(Request $request, MenuModel $menuModel)
    {
        $id = $request->input('id', 0);
        if (is_array($id)) {
            return $this->error(__('larke-admin::menu.parentid_error'));
        }
        
        $type = $request->input('type');
        
        $result = $menuModel->getList();
        
        $result = collect($result)
            ->sortBy('sort')
            ->toArray();
            
        $Tree = new Tree();
        
        if ($type == 'list') {
            $list = $Tree
                ->withConfig('parentidKey', 'pid')
                ->withConfig('buildChildKey', 'children')
                ->withData($result)
                ->build(0);
            
            $list = $Tree
                ->withConfig('parentidKey', 'pid')
                ->withConfig('buildChildKey', 'children')
                ->buildFormatList($list, $id);
        } else {
            $list = $Tree
                ->withConfig('parentidKey', 'pid')
                ->getListChildrenId($result, $id);
        }
        
        return $this->success(__('larke-admin::common.get_success'), [
            'list' => $list,
        ]);
    }
    
    /**
     * 创建
     *
     * @param  Request  $request
     * @param  MenuModel $menuModel
     * @return Response
     */
    #[RouteRule(
        title: "菜单创建", 
        desc:  "菜单创建管理",
        order: 97,
        auth:  true
    )]
    public function create(Request $request, MenuModel $menuModel)
    {
        $data = $request->all();
        if (empty($data)) {
            return $this->error(__('larke-admin::menu.menu_dont_empty'));
        }
        
        $insertStatus = $menuModel->insert($data);
        if ($insertStatus === false) {
            return $this->error(__('larke-admin::menu.create_fail'));
        }
        
        return $this->success(__('larke-admin::menu.create_success'));
    }
    
    /**
     * 更新
     *
     * @param  String $id
     * @param  Request $request
     * @param  MenuModel $menuModel
     * @return Response
     */
    #[RouteRule(
        title: "菜单更新", 
        desc:  "菜单更新管理",
        order: 96,
        auth:  true
    )]
    public function update(String $id, Request $request, MenuModel $menuModel)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::menu.menuid_dont_empty'));
        }
        
        $info = $menuModel->find($id);
        if (empty($info)) {
            return $this->error(__('larke-admin::menu.menu_not_exists'));
        }
        
        $data = $request->all();
        
        $validator = Validator::make($data, [
            'pid' => 'required',
            'sort' => 'required',
        ], [
            'pid.required' => __('larke-admin::menu.parent_menu_dont_empty'),
            'sort.required' => __('larke-admin::menu.sort_dont_empty'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        // 更新信息
        $status = $menuModel->update($id, $data);
        if ($status === false) {
            return $this->error(__('larke-admin::menu.update_fail'));
        }
        
        return $this->success(__('larke-admin::menu.update_success'));
    }
    
    /**
     * 删除
     *
     * @param  String $id
     * @param  Request $request
     * @param  MenuModel $menuModel
     * @return Response
     */
    #[RouteRule(
        title: "菜单删除", 
        desc:  "菜单删除管理",
        order: 95,
        auth:  true
    )]
    public function delete(String $id, Request $request, MenuModel $menuModel)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::menu.menuid_dont_empty'));
        }
        
        $info = $menuModel->find($id);
        if (empty($info)) {
            return $this->error(__('larke-admin::menu.menu_not_exists'));
        }
        
        $menuChildren = $menuModel->findChildren($id);
        if (! empty($menuChildren)) {
            return $this->error(__('larke-admin::menu.menu_dont_delete'));
        }
        
        $status = $menuModel->delete($id);
        if ($status === false) {
            return $this->error(__('larke-admin::menu.delete_fail'));
        }
        
        return $this->success(__('larke-admin::menu.delete_success'));
    }
    
    /**
     * 获取全部
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "菜单获取全部", 
        desc:  "菜单获取全部管理",
        order: 94,
        auth:  true
    )]
    public function getJson(Request $request, MenuModel $menuModel)
    {
        $json = $menuModel->getFileData();
        
        return $this->success(__('larke-admin::common.get_success'), [
            'json' => $json,
        ]);
    }
    
    /**
     * 保存全部
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "菜单保存全部", 
        desc:  "菜单保存全部管理",
        order: 93,
        auth:  true
    )]
    public function saveJson(Request $request, MenuModel $menuModel)
    {
        $json = $request->input('json');
        if (empty($json)) {
            return $this->error(__('larke-admin::menu.json_dont_empty'));
        }
        
        if (empty(json_decode($json, true))) {
            return $this->error(__('larke-admin::menu.json_error'));
        }
        
        $status = $menuModel->saveFileData($json);
        if ($status === false) {
            return $this->error(__('larke-admin::menu.save_fail'));
        }
        
        return $this->success(__('larke-admin::menu.save_success'));
    }
}
