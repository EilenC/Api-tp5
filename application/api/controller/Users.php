<?php
/*
 * @Author: Eilen
 * @Date: 2020/3/25
 * @Time: 5:36
 * @Created by PhpStorm
 */

namespace app\api\controller;


class Users extends Common
{
    public function index()
    {
        return json_encode([
            'title'=>'Welcome'
        ]);
    }
}