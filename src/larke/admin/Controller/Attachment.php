<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

use Larke\Admin\Annotation\RouteRule;
use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Model\Attachment as AttachmentModel;
use Larke\Admin\Service\Upload as UploadService;

/**
 * 附件
 *
 * @create 2020-10-22
 * @author deatil
 */
#[RouteRule(
    title: "附件", 
    desc:  "系统附件管理",
    order: 120,
    auth:  true,
    slug:  "{prefix}attachment"
)]
class Attachment extends Base
{
    /**
     * 列表
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "附件列表", 
        desc:  "附件列表",
        order: 100,
        auth:  true
    )]
    public function index(Request $request)
    {
        $start = (int) $request->input('start', 0);
        $limit = (int) $request->input('limit', 10);
        
        $order = $this->formatOrderBy($request->input('order', 'create_time__ASC'));
        
        $orWheres = [];
        
        $searchword = $request->input('searchword', '');
        if (! empty($searchword)) {
            $orWheres = [
                ['name', 'like', '%'.$searchword.'%'],
                ['extension', 'like', '%'.$searchword.'%'],
                ['driver', 'like', '%'.$searchword.'%'],
            ];
        }

        $wheres = [];
        
        $startTime = $this->formatDate($request->input('start_time'));
        if ($startTime !== false) {
            $wheres[] = ['create_time', '>=', $startTime];
        }
        
        $endTime = $this->formatDate($request->input('end_time'));
        if ($endTime !== false) {
            $wheres[] = ['create_time', '<=', $endTime];
        }
        
        $status = $this->switchStatus($request->input('status'));
        if ($status !== false) {
            $wheres[] = ['status', $status];
        }
        
        // 查询
        $query = AttachmentModel::wheres($wheres)
            ->orWheres($orWheres);
        
        $total = $query->count(); 
        $list = $query
            ->offset($start)
            ->limit($limit)
            ->orderBy($order[0], $order[1])
            ->get()
            ->toArray(); 
        
        return $this->success(__('larke-admin::common.get_success'), [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'list'  => $list,
        ]);
    }
    
    /**
     * 详情
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "附件详情", 
        desc:  "附件详情",
        order: 99,
        auth:  true
    )]
    public function detail(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::attachment.fileid_dont_empty'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $id])
            ->first();
        if (empty($fileInfo)) {
            return $this->error(__('larke-admin::attachment.file_empty'));
        }
        
        return $this->success(__('larke-admin::common.get_success'), $fileInfo->toArray());
    }
    
    /**
     * 删除
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "附件删除", 
        desc:  "附件删除",
        order: 98,
        auth:  true
    )]
    public function delete(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::attachment.fileid_dont_empty'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $id])
            ->first();
        if (empty($fileInfo)) {
            return $this->error(__('larke-admin::attachment.file_empty'));
        }
        
        $uploadService = UploadService::create();
        if ($uploadService === false) {
            return $this->error(__('larke-admin::attachment.file_delete_fail'));
        }
        
        $deleteStatus = $fileInfo->delete();
        if ($deleteStatus === false) {
            return $this->error(__('larke-admin::attachment.file_delete_fail'));
        }
        
        $uploadService->destroy($fileInfo['path']);
        
        return $this->success(__('larke-admin::attachment.file_delete_success'));
    }
    
    /**
     * 启用
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "附件启用", 
        desc:  "附件启用",
        order: 97,
        auth:  true
    )]
    public function enable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::attachment.fileid_dont_empty'));
        }
        
        $info = AttachmentModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::attachment.file_empty'));
        }
        
        if ($info->status == 1) {
            return $this->error(__('larke-admin::attachment.file_enabled'));
        }
        
        $status = $info->enable();
        if ($status === false) {
            return $this->error(__('larke-admin::attachment.file_enable_fail'));
        }
        
        return $this->success(__('larke-admin::attachment.file_enable_success'));
    }
    
    /**
     * 禁用
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "附件禁用", 
        desc:  "附件禁用",
        order: 96,
        auth:  true
    )]
    public function disable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::attachment.fileid_dont_empty'));
        }
        
        $info = AttachmentModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::attachment.file_empty'));
        }
        
        if ($info->status == 0) {
            return $this->error(__('larke-admin::attachment.file_disabled'));
        }
        
        $status = $info->disable();
        if ($status === false) {
            return $this->error(__('larke-admin::attachment.file_disable_fail'));
        }
        
        return $this->success(__('larke-admin::attachment.file_disable_success'));
    }
    
    /**
     * 下载码
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "附件下载码", 
        desc:  "附件下载码",
        order: 95,
        auth:  true
    )]
    public function downloadCode(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::attachment.fileid_dont_empty'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $id])
            ->first();
        if (empty($fileInfo)) {
            return $this->error(__('larke-admin::attachment.file_empty'));
        }
        
        $code = md5(mt_rand(10000, 99999) . microtime());
        Cache::put($code, $fileInfo->id, 300);
        
        return $this->success(__('larke-admin::common.get_success'), [
            'code' => $code,
        ]);
    }
    
    /**
     * 下载
     *
     * @param string $code
     * @return Response
     */
    #[RouteRule(
        title: "附件下载", 
        desc:  "附件下载",
        order: 94,
        auth:  true
    )]
    public function download(string $code)
    {
        if (empty($code)) {
            return $this->returnString(__('larke-admin::attachment.code_dont_empty'));
        }
        
        $fileId = Cache::pull($code);
        if (empty($fileId)) {
            return $this->returnString(__('larke-admin::attachment.file_empty'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $fileId])
            ->first();
        if (empty($fileInfo)) {
            return $this->returnString(__('larke-admin::attachment.file_empty'));
        }
        
        $uploadService = UploadService::create();
        if ($uploadService === false) {
            return $this->returnString(__('larke-admin::attachment.download_file_fail'));
        }
        
        if (! $uploadService->exists($fileInfo['path'])) {
            return $this->returnString(__('larke-admin::attachment.file_dont_exists'));
        }
        
        return $uploadService->getStorage()->download($fileInfo['path'], $fileInfo['name']);
    }
    
}
