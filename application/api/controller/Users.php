<?php
/*
 * @Author: Eilen
 * @Date: 2020/3/25
 * @Time: 5:36
 * @Created by PhpStorm
 */

namespace app\api\controller;


use app\api\model\Manager;
use app\api\model\Role;

class Users extends Common
{
    /**
     * 根据条件查询管理员数据
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
//        $queryInfo=[
//            "query"=> $query,
//            "pagenum"=> $pagenum,
//            "pagesize"=> $pagesize
//        ];

        $managerModel = new Manager();
//        拼接模糊查询字符串
        $query = '%' . $query . '%';
        $list = $managerModel->where('mg_name', 'like', $query)->page($pagenum, $pagesize)->select();
        if (self::is_val($list)) {
            $users = [];
        }
        foreach ($list as $item) {
            $item = $item->toArray();
//            对mg_state字段回来的数据进行下过滤
            if ($item['mg_state'] == 1) {
                $item['mg_state'] = true;
            } else {
                $item['mg_state'] = false;
            }
            $role_name = Role::where('role_id', $item['role_id'])->column("role_id,role_name");
            $info = [
                "id"          => $item['mg_id'],
                "role_name"   => $role_name[$item['role_id']],
                "username"    => $item['mg_name'],
                "create_time" => $item['mg_time'],
                "mobile"      => $item['mg_mobile'],
                "email"       => $item['mg_email'],
                "mg_state"    => $item['mg_state'],
            ];
//            array_push($data,$info);
            $users[] = $info;
        }
        $data = [
            "total"   => Manager::count(),
            "pagenum" => $pagenum,
            "users"   => $users,
        ];
        self::return_msg(200, '获取管理员列表成功!', $data);
    }

    /**
     * 添加管理员数据
     * @throws \think\Exception
     */
    public function add()
    {
        if (self::is_val(input('post.username')) ||
            self::is_val(input('post.password')) ||
            self::is_val(input('post.email')) ||
            self::is_val(input('post.mobile'))
        ) {
            self::return_msg(400, '请求参数错误!');
        }
        $username = self::clear_space(input('post.username'));
        $email = self::clear_space(input('post.email'));
        $mobile = self::clear_space(input('post.mobile'));

        $pwd_str = config('pwd_key_header') . input('post.password') . config('pwd_key_bottom');
        $password = password_hash($pwd_str, PASSWORD_BCRYPT);
        if (!$password) {
            self::return_msg(500, '添加意外错误!');
        }
        $params_info = [
            "mg_name"   => $username,
            "mg_pwd"    => $password,
            "mg_time"   => time(),
            "role_id"   => 0,
            "mg_mobile" => $mobile,
            "mg_email"  => $email,
            "mg_state"  => 0,
        ];
        $list = Manager::create($params_info);
        if (!$list) {
            self::return_msg(500, '添加意外错误!');
        }
        $list = $list->toArray();
        $data = [
            "id"          => $list['mg_id'],
            "username"    => $list['mg_name'],
            "mobile"      => $list['mg_mobile'],
            "role_id"     => $list['role_id'],
            "email"       => $list['mg_email'],
            "create_time" => $list['mg_time'],
        ];
        self::return_msg(200, '用户创建成功!', $data);
    }

    /**
     * 根据id更改用户的状态
     * @param $uid  用户id
     * @param $type 状态
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function updateState($uid, $type)
    {
        if ($type == 'true') {
            $type = 1;
        } else {
            $type = 0;
        }
        Manager::startTrans();
        try {
            $st = Manager::where('mg_id', (int)$uid)->update(['mg_state' => $type]);
            // 提交事务
            Manager::commit();
        } catch (\Exception $e) {
            // 回滚事务
            self::return_msg(500, '更新用户状态失败');
            Manager::rollback();
        }
        $list = Manager::get(['mg_id' => $uid]);
        $list = $list->toArray();
        $data = [
            "id"       => $list['mg_id'],
            "rid"      => $list['role_id'],
            "username" => $list['mg_name'],
            "mobile"   => $list['mg_mobile'],
            "email"    => $list['mg_email'],
            "mg_state" => $list['mg_state'],
        ];
        self::return_msg(200, '设置状态成功!', $data);
    }

    /**
     * 根据 ID 查询用户信息
     * @param $id
     */
    public function findById($id)
    {
        $list = $this->get_user_by_id($id);
        $data = [];
        if ($list) {
            $data = [
                "id"       => $list['mg_id'],
                "rid"      => $list['role_id'],
                "username" => $list['mg_name'],
                "mobile"   => $list['mg_mobile'],
                "email"    => $list['mg_email'],
            ];
        }
        self::return_msg(200, '查询成功!', $data);
    }

    /**
     * 编辑用户提交
     * @param $id
     * @throws \think\exception\PDOException
     */
    public function updateInfo($id)
    {
        $list = $this->get_user_by_id($id);
        if (!$list) {
            self::return_msg(400, '无此用户!', null);
        }
        if (self::is_val(input('put.email')) || self::is_val(input('put.mobile'))) {
            $data = $this->format_user_info($list, 1);
            self::return_msg(200, '更新成功!', $data);
        }
        Manager::startTrans();
        try {
            Manager::where('mg_id', (int)$id)->update([
                "mg_email"  => self::clear_space(input('put.email')),
                "mg_mobile" => self::clear_space(input('put.mobile')),
            ]);
            // 提交事务
            Manager::commit();
        } catch (\Exception $e) {
            // 回滚事务
            self::return_msg(500, '更新用户状态失败');
            Manager::rollback();
        }
        //更改了数据,所以必须重新获取一次信息
        self::return_msg(200, '更新成功!', $this->format_user_info($this->get_user_by_id($id), 1));
    }

    /**
     * 根据 ID 删除单个用户
     * @param $id
     */
    public function deleteById($id)
    {
        $result = Manager::where(['mg_id' => (int)$id])->delete();
        if ($result == 0) {
            self::return_msg(400, '无此用户!', null);
        }
        self::return_msg(200, '删除成功!', null);
    }

    /**
     * 根据 ID 分配用户角色
     * @param $id
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function editRoleById($id)
    {
        if(self::is_val(input('put.rid'))){
            self::return_msg(400,'参数错误!', null);
        }

        //开启事务
        Manager::startTrans();
        try {
            $st = Manager::where('mg_id', (int)$id)->update(['role_id' => (int)input('put.rid')]);
            // 提交事务
            Manager::commit();
        } catch (\Exception $e) {
            // 回滚事务
            self::return_msg(500, '设置角色失败!');
            Manager::rollback();
        }
        $list = Manager::get(['mg_id' => (int)$id]);
        $list = $list->toArray();

        $data = $this->format_user_info($list, 2);

        self::return_msg(200,'设置角色成功!',$data);
    }

    /**
     * 根据id查询用户
     * @param $id
     * @return array|bool
     */
    public function get_user_by_id($id)
    {
        try {
            $list = Manager::get(['mg_id' => $id])->toArray();
        } catch (\Throwable $e) {
            return false;
        }
        return $list;
    }

    /**
     * 根据类型格式化返回用户信息
     * @param $list
     * @param int $type
     * @return mixed
     */
    public function format_user_info($list, $type = 1)
    {
        $result = [
            1 => [
                "id"       => $list['mg_id'],
                "username" => $list['mg_name'],
                "role_id"  => $list['role_id'],
                "mobile"   => $list['mg_mobile'],
                "email"    => $list['mg_email'],
            ],
            2 => [
                "id"       => $list['mg_id'],
                "rid"      => $list['role_id'],
                "username" => $list['mg_name'],
                "mobile"   => $list['mg_mobile'],
                "email"    => $list['mg_email'],
            ],
        ];
        return $result[$type];
    }


}