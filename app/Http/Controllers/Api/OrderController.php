<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\Address;
use App\Model\Workers;
use App\Model\Project;
use App\Model\LeaveLog;
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

        $time = strtotime(date('Y-m-d',$request->start_time));
    	// 地址
    	$address = Address::select('name','phone','address','comment')->find($request->aid);
    	if (!$request->server_type || !$request->start_time || !$request->end_time || !$request->project_ids || !$request->countPrice) {
    		return $this->error();
    	}
    	$data['uid'] = $this->user['id'];
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
        $endtime = $request->end_time % (self::MINUTE / 60);
        if ($endtime) {
            $request->end_time = $request->start_time + (30 - $endtime) * 60;
        }else{
            $request->end_time = $request->start_time + $request->end_time;
        }
    	$data['start_time'] = date('Y-m-d H:i', $request->start_time);
    	$data['end_time'] = date('Y-m-d H:i', $request->end_time);
    	// 优惠卷
    	$coupon = 0;
    	if ($request->cid) {
    		// $coupon = Address::find($request->cid)->value('coupon');
    	}else{
    		$data['cid'] = 0;
    	}
    	$data['coupon'] = $coupon;
    	// 特殊时间服务
        $data['special'] = 0;
        $times = $time + self::HOUR * 19 + self::MINUTE;
        if ($times < $request->end_time) {
            $data['special'] = ceil(($request->end_time - $times) / 60) * self::PRICE;
        }
    	// 服务类型
    	$data['server_type'] = $request->server_type;
        // 匹配阿姨
        $sid = $this->serverId($request->server_type,$time);
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


            
            if ($sid) $newSid = $sid[0];
			// 修改订单表
			$orderInstall = Order::find($oid['id'])->update(['payment'=>$price, 'pay_type'=>self::NOTYPE, 'sid'=>$newSid]);
			// 是否添加成功，成功返回数据
			if ($orderInstall) {
				return $this->success();
			}
		}
		Order::destroy($oid['id']);
		OrderProject::where('oid', $oid['id'])->delete();
        return $this->error();
    }

    /**
     * 找阿姨
     * [serverId description]
     * @param  [type] $type     [description]
     * @param  [type] $end_time [description]
     * @return [type]           [description]
     */
    public function serverId($type, $end_time)
    {
        // 时间转换
        $end_time = date('Y-m-d',$end_time);
        $wid = Workers::where('project_ids', 'like', '%'.$type.'%')
            ->where('status', 1)
            ->pluck('id')->toArray();
        $oid = Order::whereIn('pay_type', self::ORDERTYPE)
            ->where('start_time','<=',$end_time)
            ->pluck('sid')->toArray();

        $leave = LeaveLog::with('worker')
            ->whereHas('worker',function($query) use ($type)
            {
                $query->where('project_ids','like',"%$type%");
            })
            ->where('begin_at', '<=', $end_time)
            ->where('end_at', '>=', $end_time)
            ->whereIn('status', self::ORDERTYPE)
            ->pluck('worker_id')->toArray();

        $oids = [];
        foreach ($oid as $key => $value) {
            if(strpos($value, ',')){
                $oids = array_merge($oids,explode(',', $value));
            }else{
                $oids[] = $value;
            }
        }

        $all = array_unique(array_merge($leave, $oids));
        return array_diff($wid, $all);
    }
}
