<?php

namespace App\Http\Controllers\Web;

use App\Model\Order;
use App\Model\Workers;
use App\Model\LeaveLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AppiontController extends Controller
{
    /**
     * 找没有订单数据
     */
    public function index(Request $request)
    {
    	$time = $request->time;
    	$end_time = $request->end_time;
        // 时间转换
        $time = date('Y-m-d', $time);
        $end_time = date('Y-m-d', $end_time);
        // 符合的数据
        $wid = Workers::where('status', 1)
            ->pluck('id')->toArray();
        // 正在订单的数据
        // $oid = Order::whereIn('pay_type', [0,1,2,3])
        $oid = Order::whereIn('pay_type', self::ORDERTYPE)
        	->where('start_time','>=',$time)
        	->where('end_time','<=',$end_time)
            ->pluck('sid')->toArray();
        $oidWorker = [];
        foreach ($oid as $key => $val) {
            $oidServer = array_filter(explode(',', $val));
            $oidWorker = array_merge($oidWorker, $oidServer);
        }
        $oid = $oidWorker;
        // 请假的数据
        $leave = LeaveLog::with('worker')
            ->whereHas('worker')
            ->where('begin_at', '>=', $time)
            ->where('end_at', '<=', $end_time)
            ->whereIn('status', self::ORDERTYPE)
            ->pluck('worker_id')->toArray();
        $all = array_unique(array_merge($leave, $oid));
        // 等待中的员工数据
        $workerIds = array_values(array_diff($wid, $all));
        $workerInfo = Workers::whereIn('id', $workerIds)->get()->toArray();
        return $this->success($workerInfo);
    }
    /**
     * 阿姨列表
     * [workerOrder description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function workerOrder(Request $request)
    {
    	$time = $request->time;
    	$end_time = $request->end_time;
    	// 时间转换
        $time = date('Y-m-d', $time);
        $end_time = date('Y-m-d', $end_time);
    	// 正在订单的数据
    	// $oid = Order::whereIn('pay_type', [0,1,2,3])
        $oid = Order::whereIn('pay_type', self::ORDERTYPE)
            ->where('start_time','>=',$time)
        	->where('end_time','<=',$end_time)
            ->pluck('sid')->toArray();
        $oidWorker = [];
        foreach ($oid as $key => $val) {
            $oidServer = array_filter(explode(',', $val));
            $oidWorker = array_merge($oidWorker, $oidServer);
        }
        $oids = array_unique($oidWorker);
        $workerOrderList = [];
        foreach ($oids as $key => $value) {
        	$workerUserInfo = Workers::where('id', $value)->first();
        	// $workerUserInfo['orderList'] = Order::whereIn('pay_type', [0,1,2,3])
	        $workerUserInfo['orderList'] = Order::whereIn('pay_type', self::ORDERTYPE)
	            ->where('start_time','>=',$time)
	        	->where('end_time','<=',$end_time)
	        	->where(function ($query) use ($value)
	        	{
	        		$query->where('sid', 'like', '%' . $value . ',%');
	        		$query->whereOr('sid', 'like', '%,' . $value . ',%');
	        	})
	            ->get()->toArray();
	        $workerOrderList[] = $workerUserInfo;
        }
        return $this->success($workerOrderList);
    }
    /**
     * 修改订单员工
     */
    public function save(Request $request)
    {
    	// 订单id
    	$orderid = $request->orderId;
    	// 当前员工的id
    	$sid = $request->sid;
    	// 替换后的员工id
    	$exchangeId = $request->exchangeId;
    	// 订单
    	$order = Order::where('id',$orderid)->first()->toArray();
    	// 切换后员工的能力
    	$worker1 = Workers::where('id',$exchangeId)->where('project_ids','like','%'.$order['server_type'].'%')->first();
    	if (!$worker1) {
    		// return $this->error('该员工没有这个服务');
    	}
    	$sid = array_filter(explode(',',$order['sid']));
    	//重新定义
    	$str = '';
    	foreach ($sid as $key => $val) {
    		if ($val == $sid) {
    			$str .= $exchangeId.',';
    		}else{
    			$str .= $val.',';
    		}
    	}
    	if ($str) {
    		$res = Order::where('id',$orderid)->update(['sid'=>$str]);
    		if ($res) return $this->success();
    	}
    	return $this->error('修改失败');
    }
    /**
     * 确认订单
     * [save2 description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function save2(Request $request)
    {
    	$orderid = $request->orderId;
		$res = Order::where('id',$orderid)->update(['ok'=>1]);
		if ($res) return $this->success();
    	return $this->error('修改失败');
    }
    /**
     * 当前时间
     * [linuxTime description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function linuxTime(Request $request)
    {
        return $this->success(time());
    }
}
