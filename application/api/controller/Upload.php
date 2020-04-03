<?php
/*
 * @Author: Eilen 
 * @Date: 2020/4/1 
 * @Time: 21:48
 * @Created by PhpStorm
 */

namespace app\api\controller;


use think\Exception;
use think\exception\ThrowableError;
use think\Request;

class Upload extends Common
{
    public function index()
    {
        $request = Request::instance();
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if(!$file){
            self::return_msg(400, '意外错误!', null);
        }
        try {
            $info = $file->validate(['size' => 2097152, 'ext' => 'jpg,png,gif,bmp'])->move(ROOT_PATH . 'public' . DS . config('tmp_save_path'));
        }catch (\Exception $e){
            self::return_msg(400, '意外错误!', null);
        }
        if ($info) {
            // 成功上传后 获取上传信息
            $data = [
                "tmp_path" => "tmp_uploads/".$info->getSaveName(),
                "url" => $request->domain().'/'.config('tmp_save_path').'/'.$info->getSaveName()
            ];
            self::return_msg(200, '上传成功!', $data);
        } else {
            // 上传失败获取错误信息
            echo $file->getError();
            self::return_msg(500, '上传错误!', null);
        }
    }
}