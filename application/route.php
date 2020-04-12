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

Route::post($api_base_url.'/roles/:roleId/rights', 'api/Roles/setRolesById');
//添加参数
Route::post($api_base_url.'/categories/:id/attributes', 'api/Attr/add');
//添加分类
Route::post($api_base_url.'/categories', 'api/Cate/add');
//添加商品
Route::post($api_base_url.'/goods', 'api/Good/add');
//上传图片
Route::post($api_base_url.'/upload', 'api/Upload/index');


Route::put($api_base_url.'/users/:id/role', 'api/Users/editRoleById');
Route::put($api_base_url.'/users/:uid/state/:type', 'api/Users/updateState');
Route::put($api_base_url.'/users/:id', 'api/Users/updateInfo');
Route::put($api_base_url.'/categories/:id/attributes/:attrId', 'api/Attr/updateAttr');


Route::delete($api_base_url.'/users/:id', 'api/Users/removeUserById');
Route::delete($api_base_url.'/roles/:roleId/rights/:rightId', 'api/Roles/removeRoleById');
Route::delete($api_base_url.'/goods/:id', 'api/Good/removeGoodById');
Route::delete($api_base_url.'/categories/:id/attributes/:attrId', 'api/Attr/removeAttrById');
Route::delete($api_base_url.'/categories/:id', 'api/Cate/removeCateById');


Route::get($api_base_url.'/categories/:id/attributes/:attrId', 'api/Attr/findById');
Route::get($api_base_url.'/categories/:id/attributes', 'api/Attr/index');
Route::get($api_base_url.'/categories/:id', 'api/Cate/findById');
Route::get($api_base_url.'/rights/:type', 'api/Rights/index');
Route::get($api_base_url.'/users/:id', 'api/Users/findById');
Route::get($api_base_url.'/users', 'api/Users/index');
Route::get($api_base_url.'/menus', 'api/Menus/index');
Route::get($api_base_url.'/roles', 'api/Roles/index');
Route::get($api_base_url.'/goods/:id', 'api/Good/findById');
Route::get($api_base_url.'/goods', 'api/Good/index');
Route::get($api_base_url.'/categories', 'api/Cate/index');
Route::get($api_base_url.'/orders', 'api/Orders/index');
Route::get($api_base_url.'/exp/:id', 'api/Exp/index');
Route::get($api_base_url.'/reports/type/:id', 'api/Reports/index');

return [
//     '__pattern__' => [
//         'name' => '\w+',
//     ],
//     '[hello]'     => [
//     ],
];
