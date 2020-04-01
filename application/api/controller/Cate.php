<?php
/*
 * @Author: Eilen
 * @Date: 2020/3/31
 * @Time: 11:21
 * @Created by PhpStorm
 */

namespace app\api\controller;


use app\api\model\Category;
use think\exception\ErrorException;

class Cate extends Common
{
    public function index()
    {
        $type = input('get.type');
        if (self::is_val($type)) {
            $type = 3;
        }
        if (self::is_val(input('get.pagenum')) || self::is_val(input('get.pagesize'))) {
            $list = $this->get_category_info($type);
            $data = $list;
            self::return_msg(200, '获取成功!', $data);
        }
        $pagenum = input('get.pagenum');
        $pagesize = input('get.pagesize');

        $list = $this->get_category_info((int)$type, $pagenum, $pagesize);
        $data = [
            "total"    => Category::count(),
            "pagenum"  => $pagenum,
            "pagesize" => $pagesize,
            "result"   => $list,
        ];
        self::return_msg(200, '获取成功!', $data);
    }

    /**
     * 添加分类
     * @throws \think\Exception
     */
    public function add()
    {
        if (self::is_val(input('post.cat_pid')) || self::is_val(input('post.cat_name')) || self::is_val(input('post.cat_level'))) {
            self::return_msg(400, '请求参数错误!');
        }
        $pid = input('post.cat_pid');
        $level = input('post.cat_level');
        $name = self::clear_space(input('post.cat_name'));
        $params_info = [
            "cat_name"    => $name,
            "cat_pid"     => $pid,
            "cat_level"   => $level,
            "cat_deleted" => 0,
        ];
        $list = Category::create($params_info);
        if (!$list) {
            self::return_msg(500, '添加意外错误!');
        }
        $list = $list->toArray();
        $data = $this->format_category_info($list, 1);

        self::return_msg(201, '创建成功!', $data);
    }

    /**
     * 根据 ID 查询分类
     * @param $id
     */
    public function findById($id)
    {
        if (self::is_val($id)) {
            self::return_msg(400, '请求参数错误!', null);
        }
        $list = $this->get_category_by_id($id);
        $data = $this->format_category_info($list, 1);
        self::return_msg(200, '获取成功!', $data);
    }

    /**
     * 根据 ID 软删除分类
     * @param $id
     * @throws \think\exception\PDOException
     */
    public function removeCateById($id)
    {
        try {
            Category::where('cat_id', (int)$id)->update([
                'cat_deleted' => 1,
            ]);
            //软删除
            Category::destroy((int)$id);
            // 提交事务
            Category::commit();
        } catch (\Exception $e) {
            // 回滚事务
            self::return_msg(500, '删除失败!');
            Category::rollback();
        }
        self::return_msg(200, '删除成功!', null);
    }

    /**
     * 商品分类数据列表
     * @param $type 所要获得的级数, 1/2/3
     * @param string $pagenum 分页数
     * @param string $pagesize 每页数量
     * @param array $query 根据条件查询
     * @return array  返回查询结果
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_category_info($type, $pagenum = "", $pagesize = "", $query = [])
    {
        $list0 = [];
        $list1 = [];
        $list2 = [];
        $tmp = Category::all($query);
        foreach ($tmp as $skey => $sval) {
            $sval = $sval->toArray();
            if ($sval['cat_level'] == 2) {
//                    暂存3级目录
                $list2[$sval['cat_pid']][] = [
                    "cat_id"      => $sval['cat_id'],
                    "cat_name"    => $sval['cat_name'],
                    "cat_pid"     => $sval['cat_pid'],
                    "cat_level"   => $sval['cat_level'],
                    "cat_deleted" => $sval['cat_deleted'],
                ];
            } else if ($sval['cat_level'] == 1) {
//                    暂存2级目录
                $list1[$sval['cat_pid']][] = [
                    "cat_id"      => $sval['cat_id'],
                    "cat_name"    => $sval['cat_name'],
                    "cat_pid"     => $sval['cat_pid'],
                    "cat_level"   => $sval['cat_level'],
                    "cat_deleted" => $sval['cat_deleted']
//                    "children"    => [],
                ];
            }
        }
        $cateModel = new Category();
        if (self::is_val($pagenum) || self::is_val($pagesize)) {
            $list = $cateModel->where('cat_level', 0)->order('cat_id', 'asc')->select();
        } else {
            $list = $cateModel->where('cat_level', 0)->order('cat_id', 'asc')->page($pagenum, $pagesize)->select();
        }
        foreach ($list as $item) {
            $item = $item->toArray();
            $list0[] = [
                "cat_id"      => $item['cat_id'],
                "cat_name"    => $item['cat_name'],
                "cat_pid"     => $item['cat_pid'],
                "cat_level"   => $item['cat_level'],
                "cat_deleted" => $item['cat_deleted'],
                "children"    => [],
            ];
        }

        if ($type == 3) {
            //            合并3级目录
            foreach ($list1 as $key1 => $item1) {
                foreach ($item1 as $skey => $sitem) {
                    try {
                        $list1[$key1][$skey]['children'] = $list2[$sitem['cat_id']];
                    } catch (ErrorException $e) {
                        continue;
                    }
                }
            }
        }

        if ($type == 2 || $type == 3) {
//            合并2级目录
            foreach ($list0 as $key2 => $item2) {
                try {
                    $list0[$key2]['children'] = $list1[$item2['cat_id']];
                } catch (ErrorException $e) {
                    continue;
                }
            }
        }

        $data = $list0;

        return $data;
    }

    /**
     * 根据 ID 查询分类 返回数组 失败返回false
     * @param $id
     * @return array|bool
     */
    public function get_category_by_id($id)
    {
        try {
            $list = Category::get(['cat_id' => $id])->toArray();
        } catch (\Throwable $e) {
            return false;
        }
        return $list;
    }

    /**
     * 根据指定类型 格式化数据
     * @param $list
     * @param $type
     * @return mixed
     */
    public function format_category_info($list, $type)
    {
        $result = [
            1 => [
                "cat_id"      => (int)$list['cat_id'],
                "cat_name"    => $list['cat_name'],
                "cat_pid"     => (int)$list['cat_pid'],
                "cat_level"   => (int)$list['cat_level'],
                "cat_deleted" => $list['cat_deleted'],
            ],
        ];
        return $result[$type];
    }
}