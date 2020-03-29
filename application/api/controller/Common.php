<?php

namespace app\api\controller;

use JWT;
use think\Controller;
use think\Request;

/*
 * @Author: Eilen 
 * @Date: 2020-03-24 16:54:46 
 * @Last Modified by:   Eilen 
 * @Last Modified time: 2020-03-24 16:54:46 
 */

class Common extends Controller
{
    protected $request;

    protected function _initialize()
    {
        parent::_initialize();

        $this->request = Request::instance();
//        $this->check_time($this->request->only(['time']));
//        var_dump();
        $header = Request::instance()->header();
        if (!isset($header['authorization']) || empty($header['authorization'])) {
            self::return_msg(403, '拒绝访问!');
        }

        $this->check($header['authorization']);
    }

    public function check($token)
    {
        try {
            $decoded = JWT::decode($token, config('jwt_key'), ['HS256']);
        } catch (\SignatureInvalidException $e) {
            self::return_msg(401, '拒绝访问!');
        } catch (\ExpiredException $e) {
            self::return_msg(401, 'Token已过期!');
        }

        //验证token与是否非法
        if (!($decoded->iss === config('jwt_iss'))) {
            self::return_msg(403, 'Token非法!');
        }
    }

    /**
     * 判断变量是否存在
     * @param $val
     * @return bool
     */
    static public function is_val($val)
    {
        if (!isset($val) || empty($val)) {
            return true;
        }
        return false;
    }

    static public function clear_space($str)
    {
        return str_replace(" ",'',$str);
    }

    /**
     * 统一定义的返回API
     * @param $code
     * @param string $msg
     * @param array $data
     */
    static public function return_msg($code, $msg = '', $data = [])
    {
        // 组合数据
        $rep = [
            "data" => $data,
            "meta" => [
                "msg"    => $msg,
                "status" => $code,
            ],
        ];

        echo json_encode($rep);
        die();
    }
}
