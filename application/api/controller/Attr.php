<?php
/*
 * @Author: Eilen 
 * @Date: 2020/4/1 
 * @Time: 13:44
 * @Created by PhpStorm
 */

namespace app\api\controller;


use app\api\model\Attribute;

class Attr extends Common
{
    /**
     * 查询参数列表
     * @param $id
     * @throws \think\Exception
     */
    public function index($id)
    {
        if (self::is_val($id) || self::is_val(input('get.sel'))) {
            self::return_msg(400, '请求参数错误!', null);
        }
        $sel = input('get.sel');

        try {
            $list = Attribute::all([
                "cat_id"   => (int)$id,
                "attr_sel" => $sel,
            ]);
        } catch (\Exception $e) {
            self::return_msg(500, '查询出现错误!', null);
        }

        foreach ($list as $key => $item) {
            $list[$key] = $item->toArray();
        }

        $data = $list;
        self::return_msg(200, '获取成功!', $data);
    }

    /**
     * 根据 ID 软删除参数
     * @param $id
     * @param $attrId
     * @throws \think\exception\PDOException
     */
    public function removeAttrById($id, $attrId)
    {
        if (self::is_val($id) || self::is_val($attrId)) {
            self::return_msg(400, '请求参数错误!', null);
        }
        try {
            //软删除
            Attribute::destroy((int)$attrId);
            // 提交事务
            Attribute::commit();
        } catch (\Exception $e) {
            // 回滚事务
            self::return_msg(500, '删除参数失败!');
            Attribute::rollback();
        }
        self::return_msg(200, '删除成功!', null);
    }

    /**
     * 添加动态参数或者静态属性
     * @param $id
     * @throws \think\Exception
     */
    public function add($id)
    {
        if (self::is_val(input('post.attr_name')) || self::is_val(input('post.attr_sel')) || self::is_val($id)) {
            self::return_msg(400, '请求参数错误!');
        }
        if (self::is_val(input('post.attr_vals'))) {
            $attr_vals = "";
        } else {
            $attr_vals = input('post.attr_vals');
        }
        $attr_sel = input('post.attr_sel');
        $attr_name = self::clear_space(input('post.attr_name'));
        $params_info = [
            "attr_name"  => $attr_name,
            "cat_id"     => (int)$id,
            "attr_sel"   => $attr_sel,
            "attr_write" => $attr_sel == 'many' ? 'list' : 'manual',
            "attr_vals"  => $attr_vals
        ];
        $list = Attribute::create($params_info);
        if (!$list) {
            self::return_msg(500, '添加意外错误!');
        }
        $data = $this->get_attr_by_id((int)$list->toArray()['attr_id']);
        self::return_msg(201, '创建成功!', $data);
    }

    /**
     * 根据 ID 查询参数
     * @param $id
     * @param $attrId
     */
    public function findById($id, $attrId)
    {
        if (self::is_val($id) || self::is_val($attrId)) {
            self::return_msg(400, '请求参数错误!', null);
        }
        $data = $this->get_attr_by_id($attrId);
        if (!$data) {
            self::return_msg(500, '获取异常!', null);
        }
        self::return_msg(200, '获取成功!', $data);
    }

    /**
     * 根据 ID 编辑提交参数
     * @param $id
     * @param $attrId
     * @throws \think\exception\PDOException
     */
    public function updateAttr($id,$attrId)
    {
        if (self::is_val(input('put.attr_name')) || self::is_val(input('put.attr_sel')) || self::is_val($id) || self::is_val($attrId)) {
            self::return_msg(400, '请求参数错误!');
        }
        if (self::is_val(input('put.attr_vals'))) {
            $attr_vals = "";
        } else {
            $attr_vals = input('put.attr_vals');
        }
        $attr_sel = input('put.attr_sel');
        $attr_name = self::clear_space(input('put.attr_name'));

        //开启事务
        Attribute::startTrans();
        try {
            $st = Attribute::where('attr_id', (int)$attrId)->update([
                "attr_name"=>$attr_name,
                "attr_sel"   => $attr_sel,
                "attr_write" => $attr_sel == 'many' ? 'list' : 'manual',
                "attr_vals"  => $attr_vals,
            ]);
            // 提交事务
            Attribute::commit();
        } catch (\Exception $e) {
            // 回滚事务
            self::return_msg(500, '更新失败!');
            Attribute::rollback();
        }

        $data = $this->get_attr_by_id($attrId);
        self::return_msg(200, '更新成功!', $data);
    }

    /**
     * 根据ID 返回格式化后数组
     * @param $id
     * @return array|bool
     */
    public function get_attr_by_id($id)
    {
        try {
            $list = Attribute::get(['attr_id' => $id])->toArray();
        } catch (\Throwable $e) {
            return false;
        }
        return $list;
    }
}