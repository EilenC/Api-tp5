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
use think\Exception;
use think\exception\ErrorException;

class Roles extends Common
{
    protected $rolesList;

    /**
     * 角色列表
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
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
                if (self::is_val($list1)){
                    break;
                }
                foreach ($item1 as $skey => $sitem) {
                    if(self::is_val($list2)){
                        break;
                    }
                    $list1[$key1][$skey]['children'] = $list2[$sitem['id']];
                }
            }

//            合并2级目录
            foreach ($list0 as $key2 => $item2) {
                try {
                    $list0[$key2]['children'] = $list1[$item2['id']];
                }catch (ErrorException $e){
                    continue;
                }
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

    /**
     * 根据 ID 角色授权
     * @param $roleId
     * @throws \think\exception\PDOException
     */
    public function setRolesById($roleId)
    {
        if (self::is_val($roleId)) {
            self::return_msg(400, '参数错误!', null);
        }
        if(self::is_val(input('post.rids'))){
            $rids = [];
        }else{
            $rids = explode(',', input('post.rids'));
        }
        Role::startTrans();
        $list = $this->get_role_by_id($roleId);
//        if (self::is_val($list['ps_ids'])) {
//            $oldids = [];
//        } else {
//            $oldids = explode(",", $list['ps_ids']);
//        }
        $oldids = [];
        foreach ($rids as $item) {
            if (in_array($item, $oldids)) {
                continue;
            } else {
                $oldids[] = $item;
            }
        }
        try {
            Role::where('role_id', (int)$roleId)->update([
                'ps_ids' => implode(',', $oldids),
            ]);
            // 提交事务
            Role::commit();
        } catch (\Exception $e) {
            // 回滚事务
            self::return_msg(500, '更新用户状态失败');
            Role::rollback();
        }

        self::return_msg(200, '更新成功!', null);
    }

    /**
     * 根据 ID 删除指定权限
     * @param $roleId
     * @param $rightId
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function removeRolesById($roleId, $rightId)
    {
        $list = $this->get_role_by_id($roleId);
        $new_pids = [];
        $psids = explode(',', $list['ps_ids']);
        for ($i = 0; $i < count($psids); $i++) {
            if ($psids[$i] == $rightId) {
                continue;
            }
            $new_pids[] = $psids[$i];
        }
        $new_pids = implode(',', $new_pids);
        //开启事务
        Role::startTrans();
        try {
            $st = Role::where('role_id', (int)$roleId)->update(['ps_ids' => $new_pids]);
            // 提交事务
            Role::commit();
        } catch (\Exception $e) {
            // 回滚事务
            self::return_msg(500, '设置角色失败!');
            Role::rollback();
        }
        $list0=[];
        $list1=[];
        $list2=[];
        $tmp = Permission::all($new_pids);
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
                try {
                    $list1[$key1][$skey]['children'] = $list2[$sitem['id']];
                }catch (ErrorException $e){
                    continue;
                }
            }
        }

//            合并2级目录
        foreach ($list0 as $key2 => $item2) {
            try {
                $list0[$key2]['children'] = $list1[$item2['id']];
            }catch (ErrorException $e){
                continue;
            }
        }

//            foreach ($data as $k => $v) {
//                $data[$k]['children'] = $list0;
//            }
        $data = $list0;
        self::return_msg(200, '取消权限成功!', $data);
    }

    /**
     * 根据ID查询角色
     * @param $id
     * @return array|bool
     */
    public function get_role_by_id($id)
    {
        try {
            $list = Role::get($id)->toArray();
        } catch (\Throwable $err) {
            return false;
        }
        return $list;
    }
}