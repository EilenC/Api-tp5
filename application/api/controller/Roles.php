<?php
/*
 * @Author: Eilen 
 * @Date: 2020/3/28 
 * @Time: 18:09
 * @Created by PhpStorm
 */

namespace app\api\controller;


use app\api\model\Permission;
use app\api\model\PermissionApi;
use app\api\model\Role;

class Roles extends Common
{
    protected $rolesList;

    public function index()
    {
//        处理最外层数据
        $this->rolesList = Role::all();
        if (count($this->rolesList) == 0) {
            self::return_msg(500, '意外错误!', null);
        }
        foreach ($this->rolesList as $key => $item) {
            $this->rolesList[$key]['ps_ids'] = explode(',', $item['ps_ids']);
            $data[$key] = [
                "id"       => $item['role_id'],
                "roleName" => $item['role_name'],
                "roleDesc" => $item['role_desc'],
                "children" => [],
            ];
            $tmp = Permission::all($this->rolesList[$key]['ps_ids']);
            foreach ($tmp as $skey => $sval) {
                $sval = $sval->toArray();
                if ($sval['ps_level'] == 2) {
//                    暂存3级目录
                    $list2[$sval['ps_pid']][] = [
                        "id"       => $sval['ps_id'],
                        "authName" => $sval['ps_name'],
                        "path"     => PermissionApi::where(['ps_id' => $sval['ps_id']])->find()->toArray()['ps_api_path'],
                    ];
                } else if ($sval['ps_level'] == 1) {
//                    暂存2级目录
                    $list1[$sval['ps_pid']][] = [
                        "id"       => $sval['ps_id'],
                        "authName" => $sval['ps_name'],
                        "path"     => PermissionApi::where(['ps_id' => $sval['ps_id']])->find()->toArray()['ps_api_path'],
                        "children" => [],
                    ];
                } else {
//                    暂存1级目录
                    $list0[] = [
                        "id"       => $sval['ps_id'],
                        "authName" => $sval['ps_name'],
                        "path"     => PermissionApi::where(['ps_id' => $sval['ps_id']])->find()->toArray()['ps_api_path'],
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

//            foreach ($data as $k => $v) {
//                $data[$k]['children'] = $list0;
//            }
            $data[$key]['children'] = $list0;
//            var_dump($key);
            $list0 = [];
            $list1 = [];
            $list2 = [];
        }
        self::return_msg(200, '获取成功!', $data);
    }

//    处理一级目录
    public function format_array_info($list, $father, $level)
    {
        if ($level == 0) {
            foreach ($list as $key => $val) {
                $val = $val->toArray();
                $tmp = Permission::all($val['ps_ids']);
                foreach ($tmp as $skey => $sval) {
                    $sval = $sval->toArray();
                    if ($sval['ps_level'] == 0) {
                        $father[$key]['children'][] = [
                            "id"       => $sval['ps_id'],
                            "authName" => $sval['ps_name'],
                            "path"     => PermissionApi::where(['ps_id' => $sval['ps_id']])->find()->toArray()['ps_api_path'],
                            "children" => [],
                        ];
                    }
                }
            }
        }
//        if ($level == 1) {
//            foreach ($this->rolesList as $key1 => $val1) {
//                $val1 = $val1->toArray();
//                $tmp = Permission::all($val1['ps_ids']);
//                foreach ($tmp as $key2 => $val2){
//                    $val2 = $val2->toArray();
//                    if($val2['ps_level'] == $level){
//                        foreach ($father as $key3 => $val3){
//                            foreach ($val3['children'] as $key4=>$val4){
//                                if($val4['id'] == $val2['ps_pid']){
////                                    临时存2及目录
//                                    $list2[] = [
//                                        "id"       => $val2['ps_id'],
//                                        "authName" => $val2['ps_name'],
//                                        "path"     => PermissionApi::where(['ps_id' => $val2['ps_id']])->find()->toArray()['ps_api_path'],
//                                        "children" => [],
//                                    ];
////                                    foreach ($list2 as $list2Key=>$list2val){
////                                        if($list2val['id'] == $val2['ps_id']){
////                                            array_pop($list2);
////
////                                        }
////                                    }
//                                    $father[$key3]['children'][$key4]['children'] = $list2;
////                                    $father[$key3]['children'][$key4]['children'][] = [
////                                        "id"       => $val2['ps_id'],
////                                        "authName" => $val2['ps_name'],
////                                        "path"     => PermissionApi::where(['ps_id' => $val2['ps_id']])->find()->toArray()['ps_api_path'],
////                                        "children" => [],
////                                    ];
////                                    var_dump($list2);
//
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }
        return $father;
    }

}