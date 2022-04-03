<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Model\Attachment as AttachmentModel;
use Larke\Admin\Service\Upload as UploadService;

/**
 * 附件
 *
 * @title 附件
 * @desc 系统附件管理
 * @order 570
 * @auth true
 * @slug {prefix}attachment
 *
 * @create 2020-10-22
 * @author deatil
 */
class Attachment extends Base
{
    /**
     * 列表
     *
     * @title 附件列表
     * @desc 附件列表
     * @order 501
     * @auth true
     *
     * @param  Request  $request
     * @return Response
     */
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
        
        return $this->success(__('获取成功'), [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'list' => $list,
        ]);
    }
    
    /**
     * 详情
     *
     * @title 附件详情
     * @desc 附件详情
     * @order 502
     * @auth true
     *
     * @param string $id
     * @return Response
     */
    public function detail(string $id)
    {
        if (empty($id)) {
            return $this->error(__('文件ID不能为空'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $id])
            ->first();
        if (empty($fileInfo)) {
            return $this->error(__('文件信息不存在'));
        }
        
        return $this->success(__('获取成功'), $fileInfo->toArray());
    }
    
    /**
     * 删除
     *
     * @title 附件删除
     * @desc 附件删除
     * @order 503
     * @auth true
     *
     * @param string $id
     * @return Response
     */
    public function delete(string $id)
    {
        if (empty($id)) {
            return $this->error(__('文件ID不能为空'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $id])
            ->first();
        if (empty($fileInfo)) {
            return $this->error(__('文件信息不存在'));
        }
        
        $uploadService = UploadService::create();
        if ($uploadService === false) {
            return $this->error(__('文件删除失败'));
        }
        
        $deleteStatus = $fileInfo->delete();
        if ($deleteStatus === false) {
            return $this->error(__('文件删除失败'));
        }
        
        $uploadService->destroy($fileInfo['path']);
        
        return $this->success(__('文件删除成功'));
    }
    
    /**
     * 启用
     *
     * @title 附件启用
     * @desc 附件启用
     * @order 504
     * @auth true
     *
     * @param string $id
     * @return Response
     */
    public function enable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('文件ID不能为空'));
        }
        
        $info = AttachmentModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('文件信息不存在'));
        }
        
        if ($info->status == 1) {
            return $this->error(__('文件已启用'));
        }
        
        $status = $info->enable();
        if ($status === false) {
            return $this->error(__('文件启用失败'));
        }
        
        return $this->success(__('文件启用成功'));
    }
    
    /**
     * 禁用
     *
     * @title 附件禁用
     * @desc 附件禁用
     * @order 505
     * @auth true
     *
     * @param string $id
     * @return Response
     */
    public function disable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('文件ID不能为空'));
        }
        
        $info = AttachmentModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('文件信息不存在'));
        }
        
        if ($info->status == 0) {
            return $this->error(__('文件已禁用'));
        }
        
        $status = $info->disable();
        if ($status === false) {
            return $this->error(__('文件禁用失败'));
        }
        
        return $this->success(__('文件禁用成功'));
    }
    
    /**
     * 下载码
     *
     * @title 附件下载码
     * @desc 附件下载码
     * @order 507
     * @auth true
     *
     * @param string $id
     * @return Response
     */
    public function downloadCode(string $id)
    {
        if (empty($id)) {
            return $this->error(__('文件ID不能为空'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $id])
            ->first();
        if (empty($fileInfo)) {
            return $this->error(__('文件信息不存在'));
        }
        
        $code = md5(mt_rand(10000, 99999) . microtime());
        Cache::put($code, $fileInfo->id, 300);
        
        return $this->success(__('获取成功'), [
            'code' => $code,
        ]);
    }
    
    /**
     * 下载
     *
     * @title 附件下载
     * @desc 附件下载
     * @order 508
     * @auth true
     *
     * @param string $code
     * @return Response
     */
    public function download(string $code)
    {
        if (empty($code)) {
            return $this->returnString(__('code值不能为空'));
        }
        
        $fileId = Cache::pull($code);
        if (empty($fileId)) {
            return $this->returnString(__('文件信息不存在'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $fileId])
            ->first();
        if (empty($fileInfo)) {
            return $this->returnString(__('文件信息不存在'));
        }
        
        $uploadService = UploadService::create();
        if ($uploadService === false) {
            return $this->returnString(__('下载文件失败'));
        }
        
        return $uploadService->getStorage()->download($fileInfo['path'], $fileInfo['name']);
    }
    
}
