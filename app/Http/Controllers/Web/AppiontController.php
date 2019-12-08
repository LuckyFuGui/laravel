<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AppiontController extends Controller
{
    /**
     * 找没有订单数据
     */
    public function serverId(Request $request)
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
        $workerIds = array_diff($wid, $all);
        $workerInfo = Workers::whereIn('id', $workerIds)->toArray();
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
    	// 正在订单的数据
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
	        $workerOrder = Order::whereIn('pay_type', self::ORDERTYPE)
	            ->where('start_time','>=',$time)
	        	->where('end_time','<=',$end_time)
	        	->where(function ($query)
	        	{
	        		$query->where('sid', 'like', '%' . $value . ',%');
	        		$query->whereOr('sid', 'like', '%,' . $value . ',%');
	        	})
	            ->toArray();
	        $workerOrderList[] = $workerOrder;
        }
        return $this->success($workerOrderList);
    }
}
