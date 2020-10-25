<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        if (empty($requestFile)) {
            $this->errorJson(__('上传文件不能为空'));
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
        
        $driver = config('filesystems.disks')[config('filesystems.default')]['driver'] ?? 'local';
        
        $fileInfo = AttachmentModel::where(['md5' => $md5])->first();
        if (!empty($fileInfo)) {
            @unlink($pathname);
            
            AttachmentModel::where('md5', $md5)->update([
                'update_time' => time(), 
                'update_ip' => request()->ip(),
            ]);
            
            $this->successJson(__('上传成功'), [
                'id' => $fileInfo['id'],
                'url' => $fileInfo['path'],
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
            'type_id' => config('larke.auth.adminid'),
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
            'create_time' => time(),
            'create_ip' => request()->ip(),
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
    
    /**
     * 列表
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $start = request()->get('start', 0);
        $limit = request()->get('limit', 10);
        
        $order = request()->get('order', 'DESC');
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
        
        $this->successJson(__('获取成功'), [
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
            $this->errorJson(__('文件ID不能为空'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $fileId])
            ->first();
        if (empty($fileInfo)) {
            $this->errorJson(__('文件信息不存在'));
        }
        
        $this->successJson(__('获取成功'), $fileInfo);
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
            $this->errorJson(__('文件ID不能为空'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $fileId])
            ->first();
        if (empty($fileInfo)) {
            $this->errorJson(__('文件信息不存在'));
        }
        
        $deleteStatus = AttachmentModel::where(['id' => $fileId])
            ->delete();
        if ($deleteStatus === false) {
            $this->errorJson(__('文件删除失败'));
        }
        
        Storage::disk('public')->delete($fileInfo['path']);
        
        $this->successJson(__('文件删除成功'));
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
            $this->errorJson(__('文件ID不能为空'));
        }
        
        $fileInfo = AttachmentModel::where(['id' => $fileId])
            ->first();
        if (empty($fileInfo)) {
            $this->errorJson(__('文件不存在'));
        }
        
        return Storage::disk('public')->download($fileInfo['path'], $fileInfo['name']);
    }
    
}