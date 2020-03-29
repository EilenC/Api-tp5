<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;
//接口入口地址
$api_base_url = 'api/v1';
//登录接口的路由
Route::post($api_base_url.'/login', 'api/Login/index');
//添加用户
Route::post($api_base_url.'/users', 'api/Users/add');

Route::put($api_base_url.'/users/:id/role', 'api/Users/editRoleById');
Route::put($api_base_url.'/users/:uid/state/:type', 'api/Users/updateState');
Route::put($api_base_url.'/users/:id', 'api/Users/updateInfo');


Route::delete($api_base_url.'/users/:id', 'api/Users/deleteById');

Route::get($api_base_url.'/rights/:type', 'api/Rights/index');
Route::get($api_base_url.'/users/:id', 'api/Users/findById');
Route::get($api_base_url.'/users', 'api/Users/index');
Route::get($api_base_url.'/menus', 'api/Menus/index');
Route::get($api_base_url.'/roles', 'api/Roles/index');

return [
    // '__pattern__' => [
    //     'name' => '\w+',
    // ],
    // '[hello]'     => [
    //     ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
    //     ':name' => ['index/hello', ['method' => 'post']],
    // ],
];
