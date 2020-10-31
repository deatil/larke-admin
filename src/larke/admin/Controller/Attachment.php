<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Larke\Admin\Model\Attachment as AttachmentModel;
use Larke\Admin\Service\Upload as UploadService;

/**
 * 附件
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
        
        $total = AttachmentModel::count(); 
        $list = AttachmentModel::offset($start)
            ->limit($limit)
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
    public function detail(Request $request)
    {
        $fileId = $request->get('id');
        if (empty($fileId)) {
            return $this->errorJson(__('文件ID不能为空'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $fileId])
            ->first();
        if (empty($fileInfo)) {
            return $this->errorJson(__('文件信息不存在'));
        }
        
        return $this->successJson(__('获取成功'), $fileInfo);
    }
    
    /**
     * 删除
     *
     * @param  Request  $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $fileId = $request->get('id');
        if (empty($fileId)) {
            return $this->errorJson(__('文件ID不能为空'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $fileId])
            ->first();
        if (empty($fileInfo)) {
            return $this->errorJson(__('文件信息不存在'));
        }
        
        $UploadService = (new UploadService())->initStorage();
        if ($UploadService === false) {
            return $this->errorJson(__('文件删除失败'));
        }
        
        $deleteStatus = AttachmentModel::where(['id' => $fileId])
            ->delete();
        if ($deleteStatus === false) {
            return $this->errorJson(__('文件删除失败'));
        }
        
        $UploadService->destroy($fileInfo['path']);
        
        return $this->successJson(__('文件删除成功'));
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
        
        $fileInfo = AttachmentModel::where([
            'md5' => $md5
        ])->first();
        if (!empty($fileInfo)) {
            @unlink($pathname);
            
            $fileInfo->update([
                'update_time' => time(), 
                'update_ip' => $request->ip(),
            ]);
            
            return $this->successJson(__('上传成功'), [
                'id' => $fileInfo['id'],
                'url' => $UploadService->objectUrl($fileInfo['path']),
            ]);
        }
        
        $uploadDisk = config('larke.upload.disk');
        
        $driver = config('filesystems.disks')[$uploadDisk]['driver'] ?? 'local';
        
        $mimeType = $UploadService->getMimeType($requestFile);
        
        $filetype = $UploadService->getFileType($requestFile);
        
        if ($filetype == 'image') {
            $uploadDir = config('larke.upload.directory.image');
        } else {
            $uploadDir = config('larke.upload.directory.file');
        }
        
        $path = $UploadService->dir($uploadDir)
            ->uniqueName()
            ->upload($requestFile);
        
        $data = [
            'type' => 'admin',
            'type_id' => app('larke.admin')->getId(),
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
        $Attachment = AttachmentModel::create($data);
        if ($Attachment === false) {
            $UploadService->destroy($path);
            return $this->errorJson(__('上传失败'));
        }
        
        $url = $UploadService->objectUrl($path);
        
        return $this->successJson(__('上传成功'), [
            'id' => $Attachment->id,
            'url' => $url,
        ]);
    }
    
    /**
     * 下载
     *
     * @param  Request  $request
     * @return Response
     */
    public function download(Request $request)
    {
        $fileId = $request->get('id');
        if (empty($fileId)) {
            return $this->errorJson(__('文件ID不能为空'));
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