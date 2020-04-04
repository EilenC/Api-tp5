<?php
/*
 * @Author: Eilen 
 * @Date: 2020/4/4 
 * @Time: 14:44
 * @Created by PhpStorm
 */

namespace app\api\controller;


class Exp extends Common
{
    public function index($id)
    {
        $comCode = $this->get_exp_com_code($id);
        //调用快递100 查询接口
        $result = file_get_contents('https://www.kuaidi100.com/query?type='.$comCode.'&postid='.$id.'&temp=0.2595247267684455');
        $result = json_decode($result);
        self::return_msg(200, '获取物流信息成功!', $result->data);
    }

    /**
     * 根据物流id 返回对应的物流公司comCode
     * @param $id
     * @return mixed
     */
    public function get_exp_com_code($id)
    {
        //快递100自动匹配物流公司Code
        $result = file_get_contents('https://www.kuaidi100.com/autonumber/autoComNum?resultv2=1&text=' . $id);
        $result = json_decode($result);
        return $result->auto[0]->comCode;
    }
}