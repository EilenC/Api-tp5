<?php
/*
 * @Author: Eilen 
 * @Date: 2020/3/29 
 * @Time: 15:09
 * @Created by PhpStorm
 */

namespace app\api\controller;


use app\api\model\Permission;
use app\api\model\PermissionApi;
use think\exception\HttpException;

class Rights extends Common
{
    /**
     * 根据类型所有权限列表
     * @param $type
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function index($type)
    {
        $list = Permission::all();
        $data = $this->get_rights($list, $type);
        if($data == null){
            self::return_msg(500, '返回列表错误!', $data);
        }
        self::return_msg(200, '获取权限列表成功!', $data);
    }

    /**
     * 根据类型 格式化 返回数据
     * @param $list
     * @param $type
     * @return array|null
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_rights($list, $type)
    {
        $data = null;
        if ($type == 'list') {
            foreach ($list as $item) {
                $item = $item->toArray();
                $data[] = [
                    "id"       => $item['ps_id'],
                    "authName" => $item['ps_name'],
                    "level"    => $item['ps_level'],
                    "pid"      => $item['ps_pid'],
                    "path"     => PermissionApi::where(['ps_id' => $item['ps_id']])->find()->toArray()['ps_api_path'],
                ];
            }
            return $data;
        } else if ($type == 'tree') {
//            处理树形数据
            $tmp = Permission::all();
            foreach ($tmp as $skey => $sval) {
                $sval = $sval->toArray();
                if ($sval['ps_level'] == 2) {
//                    暂存3级目录
                    $list2[$sval['ps_pid']][] = [
                        "id"       => $sval['ps_id'],
                        "authName" => $sval['ps_name'],
                        "path"     => PermissionApi::where(['ps_id' => $sval['ps_id']])->find()->toArray()['ps_api_path'],
                        "pid"      => $sval['ps_pid'],
                    ];
                } else if ($sval['ps_level'] == 1) {
//                    暂存2级目录
                    $list1[$sval['ps_pid']][] = [
                        "id"       => $sval['ps_id'],
                        "authName" => $sval['ps_name'],
                        "path"     => PermissionApi::where(['ps_id' => $sval['ps_id']])->find()->toArray()['ps_api_path'],
                        "pid"      => $sval['ps_pid'],
                        "children" => [],
                    ];
                } else {
//                    暂存1级目录
                    $list0[] = [
                        "id"       => $sval['ps_id'],
                        "authName" => $sval['ps_name'],
                        "path"     => PermissionApi::where(['ps_id' => $sval['ps_id']])->find()->toArray()['ps_api_path'],
                        "pid"      => $sval['ps_pid'],
                        "children" => [],
                    ];
                }
            }

            //            合并3级目录
            foreach ($list1 as $key1 => $item1) {
                foreach ($item1 as $skey => $sitem) {
                    $list1[$key1][$skey]['children'] = $list2[$sitem['id']];
                }
            }

//            合并2级目录
            foreach ($list0 as $key2 => $item2) {
                $list0[$key2]['children'] = $list1[$item2['id']];
            }

            //处理3级目录内pid
            foreach ($list0 as $key1 => $item1) {
                foreach ($item1['children'] as $key2 => $item2) {
                    foreach ($item2['children'] as $key3=>$item3) {
                        $list0[$key1]['children'][$key2]['children'][$key3]['pid'] = $item3['pid'].','.$item2['pid'];
                    }
                }
            }
            $data = $list0;
            return $data;
        }
    }
}