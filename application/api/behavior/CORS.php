<?php
/*
 * @Author: Eilen 
 * @Date: 2020/3/26 
 * @Time: 18:55
 * @Created by PhpStorm
 */

namespace app\api\behavior;

use think\Response;


class CORS
{
    //对应tags.php的app_init  使用驼峰命名，不能修改方法名
    public function appInit(&$params)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Content-Type,Content-Length, Authorization, Accept,X-Requested-With");
        header('Access-Control-Allow-Methods: PUT,POST,GET,DELETE,OPTIONS');
        if(request()->isOptions()){
            exit();
        }
    }
}