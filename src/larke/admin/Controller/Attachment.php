<?php

namespace Larke\Admin\Controller;

use Carbon\Carbon;

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
 * @order 108
 * @auth true
 *
 * @create 2020-10-22
 * @author deatil
 */
class Attachment extends Base
{
    /**
     * 列表
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $start = $request->get('start', 0);
        $limit = $request->get('limit', 10);
        
        $order = $request->get('order', 'DESC');
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }
        
        $searchword = $request->get('searchword', '');
        $orWheres = [];
        if (! empty($searchword)) {
            $orWheres = [
                ['name', 'like', '%'.$searchword.'%'],
                ['extension', 'like', '%'.$searchword.'%'],
                ['driver', 'like', '%'.$searchword.'%'],
            ];
        }

        $wheres = [];
        
        $startTime = $request->get('start_time');
        if (! empty($startTime)) {
            $wheres[] = ['create_time', '>=', Carbon::parse($startTime)->timestamp];
        }
        
        $endTime = $request->get('end_time');
        if (! empty($endTime)) {
            $wheres[] = ['create_time', '<=', Carbon::parse($endTime)->timestamp];
        }
        
        $total = AttachmentModel::count(); 
        $list = AttachmentModel::offset($start)
            ->limit($limit)
            ->orWheres($orWheres)
            ->wheres($wheres)
            ->orderBy('create_time', $order)
            ->get()
            ->toArray(); 
        
        return $this->successJson(__('获取成功'), [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'list' => $list,
        ]);
    }
    
    /**
     * 详情
     *
     * @param  Request  $request
     * @return Response
     */
    public function detail(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('文件ID不能为空'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $id])
            ->first();
        if (empty($fileInfo)) {
            return $this->errorJson(__('文件信息不存在'));
        }
        
        return $this->successJson(__('获取成功'), $fileInfo->toArray());
    }
    
    /**
     * 删除
     *
     * @param  Request  $request
     * @return Response
     */
    public function delete(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('文件ID不能为空'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $id])
            ->first();
        if (empty($fileInfo)) {
            return $this->errorJson(__('文件信息不存在'));
        }
        
        $UploadService = (new UploadService())->initStorage();
        if ($UploadService === false) {
            return $this->errorJson(__('文件删除失败'));
        }
        
        $deleteStatus = $fileInfo->delete();
        if ($deleteStatus === false) {
            return $this->errorJson(__('文件删除失败'));
        }
        
        $UploadService->destroy($fileInfo['path']);
        
        return $this->successJson(__('文件删除成功'));
    }
    
    /**
     * 启用
     *
     * @param  Request  $request
     * @return Response
     */
    public function enable(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = AttachmentModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        if ($info->status == 1) {
            return $this->errorJson(__('信息已启用'));
        }
        
        $status = $info->enable();
        if ($status === false) {
            return $this->errorJson(__('启用失败'));
        }
        
        return $this->successJson(__('启用成功'));
    }
    
    /**
     * 禁用
     *
     * @param  Request  $request
     * @return Response
     */
    public function disable(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = AttachmentModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        if ($info->status == 0) {
            return $this->errorJson(__('信息已禁用'));
        }
        
        $status = $info->disable();
        if ($status === false) {
            return $this->errorJson(__('禁用失败'));
        }
        
        return $this->successJson(__('禁用成功'));
    }
    
    /**
     * 上传文件
     *
     * @param  Request  $request
     * @return Response
     */
    public function upload(Request $request)
    {
        $requestFile = $request->file('file');
        if (empty($requestFile)) {
            return $this->errorJson(__('上传文件不能为空'));
        }
        
        // Pathname
        $pathname = $requestFile->getPathname();
        
        // 原始名称
        $name = $requestFile->getClientOriginalName();
        
        // mimeType
        $mimeType = $requestFile->getClientMimeType();
        
        // 扩展名
        $extension = $requestFile->extension();
        
        // 大小
        $size = $requestFile->getSize();
        
        $md5 = hash_file('md5', $pathname);
        
        $sha1 = hash_file('sha1', $pathname);
        
        $UploadService = (new UploadService())->initStorage();
        if ($UploadService === false) {
            return $this->errorJson(__('上传文件失败'));
        }
        
        $uploadDisk = config('larke.upload.disk');
        
        $driver = $uploadDisk ?: 'local';
        
        $mimeType = $UploadService->getMimeType($requestFile);
        
        $filetype = $UploadService->getFileType($requestFile);
        
        $fileInfo = AttachmentModel::where([
            'md5' => $md5
        ])->first();
        if (!empty($fileInfo)) {
            @unlink($pathname);
            
            $fileInfo->update([
                'update_time' => time(), 
                'update_ip' => $request->ip(),
            ]);
            
            $res = [
                'id' => $fileInfo['id'],
            ];
            if (in_array($filetype, ['image', 'video', 'audio'])) {
                $res['url'] = $fileInfo['url'];
            }
            
            return $this->successJson(__('上传成功'), $res);
        }
        
        if ($filetype == 'image') {
            $uploadDir = config('larke.upload.directory.image');
        } elseif ($filetype == 'video' || $filetype == 'audio') {
            $uploadDir = config('larke.upload.directory.media');
        } else {
            $uploadDir = config('larke.upload.directory.file');
        }
        
        $path = $UploadService->dir($uploadDir)
            ->uniqueName()
            ->upload($requestFile);
        
        $data = [
            'belong_type' => AdminModel::class,
            'belong_id' => app('larke.admin')->getId(),
            'name' => $name,
            'path' => $path,
            'mime' => $mimeType,
            'extension' => $extension,
            'size' => $size,
            'md5' => $md5,
            'sha1' => $sha1,
            'driver' => $driver,
            'status' => 1,
        ];
        $attachment = AttachmentModel::create($data);
        if ($attachment === false) {
            $UploadService->destroy($path);
            return $this->errorJson(__('上传失败'));
        }
        
        $url = $UploadService->objectUrl($path);
        
        $res = [
            'id' => $attachment->id,
        ];
        if (in_array($filetype, ['image', 'video', 'audio'])) {
            $res['url'] = $url;
        }
        
        return $this->successJson(__('上传成功'), $res);
    }
    
    /**
     * 下载码
     *
     * @param  Request  $request
     * @return Response
     */
    public function downloadCode(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('文件ID不能为空'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $id])
            ->first();
        if (empty($fileInfo)) {
            return $this->errorJson(__('文件不存在'));
        }
        
        $code = md5(mt_rand(10000, 99999) . microtime());
        Cache::put($code, $fileInfo->id, 300);
        
        return $this->successJson(__('获取成功'), [
            'code' => $code,
        ]);
    }
    
    /**
     * 下载
     *
     * @param  Request  $request
     * @return Response
     */
    public function download(string $code, Request $request)
    {
        if (empty($code)) {
            return $this->errorJson(__('code值不能为空'));
        }
        
        $fileId = Cache::pull($code);
        if (empty($fileId)) {
            return $this->errorJson(__('文件不存在'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $fileId])
            ->first();
        if (empty($fileInfo)) {
            return $this->errorJson(__('文件不存在'));
        }
        
        $UploadService = (new UploadService())->initStorage();
        if ($UploadService === false) {
            return $this->errorJson(__('下载文件失败'));
        }
        
        return $UploadService->getStorage()->download($fileInfo['path'], $fileInfo['name']);
    }
    
}
