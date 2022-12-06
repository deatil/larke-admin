<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Exception;

use Illuminate\Http\Request;

use Larke\Admin\Annotation\Route;
use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Model\Attachment as AttachmentModel;
use Larke\Admin\Service\Upload as UploadService;

/**
 * 上传
 *
 * @create 2021-4-18
 * @author deatil
 */
#[Route(
    title: "附件上传", 
    desc:  "附件上传",
    order: 571,
    auth:  true,
    slug:  "{prefix}upload"
)]
class Upload extends Base
{
    
    /**
     * 上传文件
     *
     * @param  Request  $request
     * @return Response
     */
    #[Route(
        title: "上传文件", 
        desc:  "上传附件文件",
        order: 572,
        auth:  true
    )]
    public function file(Request $request)
    {
        $requestFile = $request->file('file');
        if (empty($requestFile)) {
            return $this->error(__('上传文件不能为空'));
        }
        
        // 上传的文件临时位置
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
        
        try {
            $uploadService = UploadService::create();
        } catch(Exception $e) {
            return $this->error(__('上传文件失败'));
        }
        
        $uploadDisk = config('larkeadmin.upload.disk');
        
        $driver = $uploadDisk ?: 'local';
        
        $mimeType = $uploadService->getMimeType($requestFile);
        
        $filetype = $uploadService->getFileType($requestFile);
        
        $fileInfo = AttachmentModel::byMd5($md5)->first();
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
            
            return $this->success(__('上传文件成功'), $res);
        }
        
        if ($filetype == 'image') {
            $uploadDir = config('larkeadmin.upload.directory.image');
        } elseif ($filetype == 'video' || $filetype == 'audio') {
            $uploadDir = config('larkeadmin.upload.directory.media');
        } else {
            $uploadDir = config('larkeadmin.upload.directory.file');
        }
        
        try {
            $path = $uploadService->dir($uploadDir)
                ->uniqueName()
                ->upload($requestFile);
        } catch(Exception $e) {
            return $this->error(__('上传文件失败'));
        }

        // 附件入库数据库
        $adminId = app('larke-admin.auth-admin')->getId();
        $attachmentModel = AdminModel::where('id', $adminId)
            ->first()
            ->attachments();
        $attachment = $attachmentModel->create([
            'name' => $name,
            'path' => $path,
            'mime' => $mimeType,
            'extension' => $extension,
            'size' => $size,
            'md5' => $md5,
            'sha1' => $sha1,
            'driver' => $driver,
            'status' => 1,
        ]);
        if ($attachment === false) {
            // 入库信息失败删除已上传文件
            $uploadService->destroy($path);
            
            return $this->error(__('上传文件失败'));
        }
        
        $res = [
            'id' => $attachment->id,
        ];
        if (in_array($filetype, ['image', 'video', 'audio'])) {
            $url = $uploadService->objectUrl($path);
            
            $res['url'] = $url;
        }
        
        return $this->success(__('上传文件成功'), $res);
    }
}
