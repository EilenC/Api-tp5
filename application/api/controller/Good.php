<?php
/*
 * @Author: Eilen 
 * @Date: 2020/3/30 
 * @Time: 18:23
 * @Created by PhpStorm
 */

namespace app\api\controller;


use app\api\model\Attribute;
use app\api\model\Goods;
use app\api\model\GoodsAttr;
use app\api\model\GoodsPics;
use app\api\model\Role;
use think\Image;
use think\Request;

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
        $list = $goodsModel->where('goods_name', 'like', $query)->order('goods_id', 'desc')->page($pagenum, $pagesize)->select();
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
                "goods_id"     => $item['goods_id'],
                "cat_id"       => $item['cat_id'],
                "goods_name"   => $item['goods_name'],
                "goods_price"  => $item['goods_price'],
                "goods_number" => $item['goods_number'],
                "goods_weight" => $item['goods_weight'],
                "goods_state"  => $item['goods_state'],
                "add_time"     => $item['add_time'],
                "upd_time"     => $item['upd_time'],
                "hot_mumber"   => $item['hot_mumber'],
                "is_promote"   => $item['is_promote'],
                "cat_one_id"   => $item['cat_one_id'],
                "cat_two_id"   => $item['cat_two_id'],
                "cat_three_id" => $item['cat_three_id'],
                "goods_pic" => GoodsPics::where(['goods_id'=>(int)$item['goods_id']])->column('pics_sma')[0],
                "pic" => GoodsPics::where(['goods_id'=>(int)$item['goods_id']])->column('pics')[0]
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
     * 添加商品
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $request = Request::instance();
        $postData = input('post.');
        if (self::is_val($postData['goods_name']) ||
            self::is_val($postData['goods_price']) ||
            self::is_val($postData['goods_weight']) ||
            self::is_val($postData['goods_number']) ||
            self::is_val($postData['goods_cat'])
        ) {
            self::return_msg(400, '请求参数错误!');
        }

        //处理下post数据里的空格
        foreach ($postData as $key => $item) {
            $postData[$key] = self::clear_space($item);
        }

        //根据商品名称判断商品是否已在数据库
//        if (Goods::get(['goods_name' => $postData['goods_name']])) {
//            self::return_msg(400, '添加的商品已经存在!', null);
//        }

        $arrCatId = explode(',', $postData['goods_cat']);
        if (self::is_val($postData['goods_introduce'])) {
            $postData['goods_introduce'] = "";
        }
        $params_info = [
            "goods_name"       => $postData['goods_name'],
            "goods_price"      => $postData['goods_price'],
            "goods_weight"     => $postData['goods_weight'],
            "goods_number"     => $postData['goods_number'],
            "cat_id"           => $arrCatId[2],
            "goods_introduce"  => $postData['goods_introduce'],
            "goods_big_logo"   => "",
            "goods_small_logo" => "",
            "add_time"         => time(),
            "upd_time"         => time(),
            "cat_one_id"       => $arrCatId[0],
            "cat_two_id"       => $arrCatId[1],
            "cat_three_id"     => $arrCatId[2],
        ];

        // 启动事务
        Goods::startTrans();
        try {
            $list = Goods::create($params_info);
            //处理需要保存的图片
            if(!self::is_val($postData['pics'])){
                foreach ($postData['pics'] as $item) {
                    $imgSrc = str_replace('\\','/',ROOT_PATH.'public/'.$item['pic']);
                    $imgPath = explode('/',$imgSrc);
                    $fileName = explode('.',$imgPath[count($imgPath)-1]);
                    $bigfileNamePath = $fileName[0].'_800x800.'.$fileName[1];
                    $midfileNamePath = $fileName[0].'_400x400.'.$fileName[1];
                    $smafileNamePath = $fileName[0].'_200x200.'.$fileName[1];

                    $image = Image::open($imgSrc);
                    $image->water(ROOT_PATH.'public/'.config('logo')['32'],Image::WATER_NORTHWEST,40)
                        ->text(config('text'),ROOT_PATH.'public/simhei.ttf',20,'#AAAAAA',Image::WATER_SOUTH)
                        ->save(str_replace('\\','/',ROOT_PATH.'public/'.config('save_path').'/'.$imgPath[count($imgPath)-1]));
                    unset($image);

                    $image = Image::open($imgSrc);
                    $image->thumb(800,800)->water(ROOT_PATH.'public/'.config('logo')['64'],Image::WATER_NORTHWEST,40)
                        ->text(config('text'),ROOT_PATH.'public/simhei.ttf',20,'#AAAAAA',Image::WATER_SOUTH)
                        ->save(str_replace('\\','/',ROOT_PATH.'public/'.config('save_path').'/'.$bigfileNamePath));
                    unset($image);

                    $image = Image::open($imgSrc);
                    $image->thumb(400,400)->water(ROOT_PATH.'public/'.config('logo')['32'],Image::WATER_NORTHWEST,40)
                        ->text(config('text'),ROOT_PATH.'public/simhei.ttf',15,'#AAAAAA',Image::WATER_SOUTH)
                        ->save(str_replace('\\','/',ROOT_PATH.'public/'.config('save_path').'/'.$midfileNamePath));
                    unset($image);

                    $image = Image::open($imgSrc);
                    $image->thumb(200,200)->water(ROOT_PATH.'public/'.config('logo')['16'],Image::WATER_NORTHWEST,40)
                        ->text(config('text'),ROOT_PATH.'public/simhei.ttf',10,'#AAAAAA',Image::WATER_SOUTH)
                        ->save(str_replace('\\','/',ROOT_PATH.'public/'.config('save_path').'/'.$smafileNamePath));
                    unset($image);
                }

                $picInfo = [
                    "goods_id" => (int)$list->toArray()['goods_id'],
                    "pics" => $request->domain().'/'.config('save_path').'/'.$imgPath[count($imgPath)-1],
                    "pics_big" => $request->domain().'/'.config('save_path').'/'.$bigfileNamePath,
                    "pics_mid" => $request->domain().'/'.config('save_path').'/'.$midfileNamePath,
                    "pics_sma" => $request->domain().'/'.config('save_path').'/'.$smafileNamePath
                ];
                GoodsPics::create($picInfo);
            }
            //处理需要保存的参数与属性
            if(!self::is_val($postData['attrs'])){
                foreach ($postData['attrs'] as $item) {
                    $attrInfo  = [
                        "goods_id"=>(int)$list->toArray()['goods_id'],
                        "attr_id" => (int)$item['attr_id'],
                        "attr_value" => $item['attr_value']
                    ];
                    GoodsAttr::create($attrInfo);
                }
            }
            // 提交事务
            Goods::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Goods::rollback();
            self::return_msg(500, '添加出现意外错误!', null);
        }

        $data = $this->get_good_by_id((int)$list->toArray()['goods_id']);
        self::return_msg(201, '创建商品成功!', $data);
    }

    /**
     * 根据 ID 查询商品
     * @param $id
     */
    public function findById($id)
    {
        if (self::is_val($id)) {
            self::return_msg(400, '请求参数错误!', null);
        }

        $data = $this->get_good_by_id((int)$id);

        if (!$data) {
            self::return_msg(500, '获取异常!', null);
        }
        self::return_msg(200, '获取成功!', $data);
    }

    /**
     * 根据 ID 软删除商品
     * @param $id
     * @throws \think\exception\PDOException
     */
    public function removeGoodById($id)
    {
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
            self::return_msg(500, '更新用户状态失败!');
            Goods::rollback();
        }
        self::return_msg(200, '删除成功!', null);
    }


    public function get_good_by_id($id)
    {
        $list = Goods::get(['goods_id' => $id])->toArray();
        if(self::is_val($list)){
            self::return_msg(500, '查询出现意外错误!',null);
        }
        $list['goods_cat'] = $list['cat_one_id'] . ',' . $list['cat_two_id'] . ',' . $list['cat_three_id'];
        $pics = GoodsPics::all(['goods_id' => $list['goods_id']]);
        if(self::is_val($pics)){
            $list['pics'] = [];
//            self::return_msg(500, '查询出现意外错误!',null);
        }else{
            foreach ($pics as $item) {
                $list['pics'][] = [
                    "pics_id"      => $item['pics_id'],
                    "goods_id"     => $item['goods_id'],
                    "pics_big"     => $item['pics_big'],
                    "pics_mid"     => $item['pics_mid'],
                    "pics_sma"     => $item['pics_sma'],
                    "pics_url"     => $item['pics'],
                    "pics_big_url" => $item['pics_big'],
                    "pics_mid_url" => $item['pics_mid'],
                    "pics_sma_url" => $item['pics_sma'],
                ];
            }
        }

        $attrs = GoodsAttr::all(['goods_id' => $list['goods_id']]);
        if(self::is_val($attrs)){
            $list['attrs'] =[];
//            self::return_msg(500, '查询出现意外错误!',null);
        }else{
            foreach ($attrs as $item) {
                $res = Attribute::get(['attr_id' => $item['attr_id']])->toArray();
                if(self::is_val($res)){
//                    self::return_msg(500, '查询出现意外错误!',null);
                    $res['attr_name'] = '';
                    $res['attr_sel'] = '';
                    $res['attr_write'] = '';
                    $res['attr_vals'] = '';
                }
                $list['attrs'][] = [
                    "goods_id"   => $item['goods_id'],
                    "attr_id"    => $item['attr_id'],
                    "attr_value" => $item['attr_value'],
                    "add_price"  => (int)$item['add_price'],
                    "attr_name"  => $res['attr_name'],
                    "attr_sel"   => $res['attr_sel'],
                    "attr_write" => $res['attr_write'],
                    "attr_vals"  => $res['attr_vals'],
                ];
            }
        }

        return $list;
    }
}