<?php

namespace app\api\controller;

use app\api\controller\Common;
use app\api\model\Manager;
use JWT;

/*
 * @Author: Eilen 
 * @Date: 2020-03-24 11:24:53 
 * @Last Modified by: Eilen
 * @Last Modified time: 2020-03-24 11:43:59
 */

class Login
{
    //登录生成token1
    public function index()
    {
        $data = input('post.');
        if (!isset($data['username']) || empty($data['username']) || !isset($data['password']) || empty($data['password'])) {
            Common::return_msg(400, '无用户名或者密码!');
        }
        $username = $data['username'];
        $password = $data['password'];
        //从数据库中
        $list = Manager::get([
            'mg_name' => $username,
        ]);
        if (!isset($list)) {
            Common::return_msg(400, '帐号不存在!');
        }
        $list = $list->toArray();
        $pwd_str = config('pwd_key_header') . $password . config('pwd_key_bottom');
        if (!password_verify($pwd_str, $list['mg_pwd'])) {
            Common::return_msg(400, '密码不匹配!');
        }
        $time = time();
        $payload = [
            "uid" => $list['mg_id'],
            "username" => $list['mg_name'],
            "iss"  => config('jwt_iss'),
            "aud"  => "http://client.eilen.top",
            "iat"  => $time,
            "nbf"  => $time,
            "exp"  => $time + config('jwt_exp'),
        ];

        $token = $jwt = JWT::encode($payload, config('jwt_key'));

        $rep = [
            "id"       => $list['mg_id'],
            "rid"      => $list['role_id'],
            "username" => $list['mg_name'],
            "mobile"   => $list['mg_mobile'],
            "email"    => $list['mg_email'],
            "token"    => $token,
        ];
        //登录成功
        Common::return_msg(200, '登录成功!', $rep);

    }

}
