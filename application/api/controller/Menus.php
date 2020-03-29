<?php
/*
 * @Author: Eilen 
 * @Date: 2020/3/27 
 * @Time: 22:42
 * @Created by PhpStorm
 */

namespace app\api\controller;


use app\api\model\Permission;
use app\api\model\PermissionApi;

class Menus extends Common
{
    protected $pslist;
    protected $psalist;

    public function index()
    {
        self::return_msg(200, '获取菜单列表成功!',$this->format_menus());
    }

    /**
     * 指定id查询 path order 返回对应的数组
     * @param $ps_id    查询条件
     * @return array
     */
    public function get_menus_path($ps_id)
    {
        foreach ($this->psalist as $item) {
            $item = $item->toArray();
            if ($item['ps_id'] == $ps_id) {
                return $item;
            }
        }
        return [];
    }

    /**
     * 将目录列表从数据库中取出并且进行格式化
     * @return array
     */
    public function format_menus()
    {
        $permissionModel = new Permission();
        $this->psalist = PermissionApi::all();
//        将一二级目录通过where 筛选出来
        $this->pslist = $permissionModel->where('ps_level', 0)->whereOr('ps_level', 1)->select();


        $data = [];
//        将一级目录进行剥离
        foreach ($this->pslist as $item) {
            $item = $item->toArray();
            if ($item['ps_pid'] == 0) {
                $data[] = [
                    "id"       => $item['ps_id'],
                    "authName" => $item['ps_name'],
                    "path"     => $this->get_menus_path($item['ps_id'])['ps_api_path'],
                    "children" => [],
                    "order"    => $this->get_menus_path($item['ps_id'])['ps_api_order'],
                ];
            }
        }
//        处理子目录
        foreach ($this->pslist as $item) {
            $item = $item->toArray();
            if($item['ps_level'] != 0){
                foreach ($data as $key=>$val){
                    if($val['id']==$item['ps_pid']){
                        $data[$key]['children'][] = [
                            "id"       => $item['ps_id'],
                            "authName" => $item['ps_name'],
                            "path"     => $this->get_menus_path($item['ps_id'])['ps_api_path'],
                            "children" => [],
                            "order"    => $this->get_menus_path($item['ps_id'])['ps_api_order'],
                        ];
                    }
                }
            }
        }
//        根据order字段进行排序
        array_multisort(array_column($data, 'order'), SORT_ASC, $data);
        return $data;
    }

}