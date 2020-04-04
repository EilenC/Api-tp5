<?php
/*
 * @Author: Eilen 
 * @Date: 2020/4/3 
 * @Time: 22:31
 * @Created by PhpStorm
 */

namespace app\api\controller;


use app\api\model\Order;

class Orders extends Common
{
    public function index()
    {
        if (self::is_val(input('get.pagenum')) || self::is_val(input('get.pagesize'))) {
            self::return_msg(400, '请求参数错误!');
        }
        $query = input('get.query');
        if (self::is_val($query)) {
            $query = '';
        }
        $pagenum = input('get.pagenum');
        $pagesize = input('get.pagesize');

        $ordersModel = new Order();
//        拼接模糊查询字符串
        $query = '%' . $query . '%';
        $list = $ordersModel->where('order_number', 'like', $query)->order('order_id', 'desc')->page($pagenum, $pagesize)->select();
        if (self::is_val($list)) {
            $orders = [];
        }
        foreach ($list as $item) {
            $item = $item->toArray();
            $orders[] = $item;
        }
        $data = [
            "total"   => Order::count(),
            "pagenum" => $pagenum,
            "goods"   => $orders,
        ];
        self::return_msg(200, '获取成功!', $data);
    }
}