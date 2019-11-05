<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\Address;
use App\Model\Project;
use App\Model\OrderProject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
	/**
     * 添加
     * [store description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function store(Request $request)
    {
    	// 地址
    	$address = Address::select('name','phone','address','comment')->find($request->aid);
    	if (!$request->server_type || !$request->start_time || !$request->end_time || !$request->project_ids || !$request->countPrice || !$request->uid) {
    		return $this->error();
    	}
    	$data['uid'] = $request->uid;
    	$data['name'] = $address['name'];
    	$data['phone'] = $address['phone'];
    	$data['address'] = $address['address'];
    	$data['comment'] = $address['comment'];
    	// 点单号
    	$data['order_sn'] = 'wx' . date('YmdHis') . rand(10000, 99999);
    	// 查询数据的条件
    	$in = [];
    	foreach ($request->project_ids as $k => $v) {
    		$in[] = $k;
    	}
    	// 项目查询
    	$project = Project::where('type', $request->server_type)
    			->where('state', self::TYPE)
    			->whereIn('id',$in)
    			->get();
    	if (!$project) return $this->error();
    	// 开始和结束时间
    	$data['start_time'] = $request->start_time;
    	$data['end_time'] = $request->end_time;
    	// 优惠卷
    	$coupon = 0;
    	if ($request->cid) {
    		// $coupon = Address::find($request->cid)->value('coupon');
    	}else{
    		$data['cid'] = 0;
    	}
    	$data['coupon'] = $coupon;
    	// 特殊时间服务
    	$data['special'] = !empty($request->special) ? $request->special : 0;
    	// 服务类型
    	$data['server_type'] = $request->server_type;
    	// 添加获取id
    	$oid = Order::create($data);
    	// 更新价格，加入详情单
    	$price = $data['special'];
		foreach ($project as $key => $value) {
			$OrderProject['pid'] = $value['id'];
			$OrderProject['oid'] = $oid['id'];
			$OrderProject['price'] = $value['price'];
			$OrderProject['name'] = $value['serverName'];
			$OrderProject['num'] = $request->project_ids[$value['id']];
			$rester = OrderProject::create($OrderProject);
			// 计算总价格
			if ($rester) {
				$price = $price + $value['price'] * $request->project_ids[$value['id']];
			}
		}
		if ($price == $request->countPrice) {
			// 修改订单表
			$orderInstall = Order::find($oid['id'])->update(['payment'=>$price, 'pay_type'=>self::NOTYPE]);
			// 是否添加成功，成功返回数据
			if ($orderInstall) {
				return $this->success();
			}
		}
		Order::destroy($oid['id']);
		OrderProject::where('oid', $oid['id'])->delete();
        return $this->error();
    }
}
