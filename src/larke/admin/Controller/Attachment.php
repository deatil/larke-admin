<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Larke\Admin\Model\Attachment as AttachmentModel;

/**
 * 附件
 *
 * @create 2020-10-22
 * @author deatil
 */
class Attachment extends Base
{
    /**
     * 上传文件
     *
     * @param  Request  $request
     * @return Response
     */
    public function upload(Request $request)
    {
        $requestFile = $request->file('file');
        
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
        
        $driver = config('filesystems.disks')[config('filesystems.default')]['driver'] ?? 'local';
        
        $fileInfo = AttachmentModel::where(['md5' => $md5])->first();
        if (!empty($fileInfo)) {
            $this->successJson(__('上传成功'), [
                'id' => $fileInfo['id'],
                'url' => Storage::url($fileInfo['path']),
            ]);
        }
        
        $path = $request->file('file')->storeAs(
            'larke', 
            md5(time().mt_rand(100000, 999999)).'.'.$extension,
            'public'
        );
        
        $id = md5(mt_rand(100000, 999999).microtime());
        $data = [
            'id' => $id,
            'type' => 'admin',
            'type' => config('larke.auth.adminid'),
            'name' => $name,
            'path' => $path,
            'mime' => $mimeType,
            'extension' => $extension,
            'size' => $size,
            'md5' => $md5,
            'sha1' => $sha1,
            'driver' => $driver,
            'status' => 1,
            'update_time' => time(),
            'update_ip' => request()->ip(),
            'add_time' => time(),
            'add_ip' => request()->ip(),
        ];
        $status = AttachmentModel::insert($data);
        if ($status === false) {
            Storage::disk('public')->delete($path);
            $this->errorJson(__('上传失败'));
        }
        
        $url = Storage::url($path);
        
        $this->successJson(__('上传成功'), [
            'id' => $id,
            'url' => $url,
        ]);
    }
}