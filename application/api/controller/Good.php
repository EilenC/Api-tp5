<?php
/*
 * @Author: Eilen 
 * @Date: 2020/3/30 
 * @Time: 18:23
 * @Created by PhpStorm
 */

namespace app\api\controller;


use app\api\model\Goods;
use app\api\model\Role;

class Good extends Common
{
    /**
     * 商品列表数据
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        if (self::is_val(input('get.pagenum')) || self::is_val(input('get.pagesize'))) {
            self::return_msg(400, '请求参数错误!');
        }
        $query = input('get.query');
        if (!isset($query)) {
            self::return_msg(400, '请求参数错误');
        }
        $pagenum = input('get.pagenum');
        $pagesize = input('get.pagesize');

        $goodsModel = new Goods();
//        拼接模糊查询字符串
        $query = '%' . $query . '%';
        $list = $goodsModel->where('goods_name', 'like', $query)->order('goods_id','desc')->page($pagenum, $pagesize)->select();
        if (self::is_val($list)) {
            $goods = [];
        }
        foreach ($list as $item) {
            $item = $item->toArray();
//            对is_promote字段回来的数据进行下过滤
            if ($item['is_promote'] == 1) {
                $item['is_promote'] = true;
            } else {
                $item['is_promote'] = false;
            }

            $info = [
                "goods_id"          => $item['goods_id'],
                "cat_id"          => $item['cat_id'],
                "goods_name"   => $item['goods_name'],
                "goods_price"    => $item['goods_price'],
                "goods_number" => $item['goods_number'],
                "goods_weight"      => $item['goods_weight'],
                "goods_state"       => $item['goods_state'],
                "add_time"    => $item['add_time'],
                "upd_time"    => $item['upd_time'],
                "hot_mumber"    => $item['hot_mumber'],
                "is_promote"    => $item['is_promote'],
                "cat_one_id"    => $item['cat_one_id'],
                "cat_two_id"    => $item['cat_two_id'],
                "cat_three_id"    => $item['cat_three_id'],
            ];
//            array_push($data,$info);
            $goods[] = $info;
        }
        $data = [
            "total"   => Goods::count(),
            "pagenum" => $pagenum,
            "goods"   => $goods,
        ];
        self::return_msg(200, '获取成功!', $data);
    }

    /**
     * 根据 ID 软删除商品
     * @param $id
     * @throws \think\exception\PDOException
     */
    public function removeGoodsById($id){
        try {
            Goods::where('goods_id', (int)$id)->update([
                'is_del' => 1,
            ]);
            //软删除
            Goods::destroy((int)$id);
            // 提交事务
            Goods::commit();
        } catch (\Exception $e) {
            // 回滚事务
            self::return_msg(500, '更新用户状态失败');
            Goods::rollback();
        }
        self::return_msg(200, '删除成功!', null);
    }
}