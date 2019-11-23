<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\Workers;
use App\Model\LeaveLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TimedateController extends Controller
{
    // 时间列表
    public function index(Request $request)
    {
        // 具体天数
        $day = $request->day;
        // 服务类型
        $type = $request->type;
        // 返回时间
        $date = [];
        // 总员工人数
        $countUser = $this->countUser($type);
        // 时间计算
        for ($i = 7; $i <= 22; $i++) {
            // 查询请假人数
            $leaveUser = $this->leaveUser($day + self::HOUR * $i, $type);
            // 订单人数
            $orderUser = $this->orderUser();
            // 结束订单后
            $suspend = $this->suspend($day + self::HOUR * $i, $type);
            // 现在剩余人数
            $date[$i . ":00"] = $countUser - $leaveUser - $orderUser - $suspend;
            // *****************
            // *** 半小时处理 ***
            // *****************
            // 查询请假人数
            $leaveUser = $this->leaveUser($day + self::HOUR * $i + self::MINUTE, $type);
            // 订单人数
            $orderUser = $this->orderUser($day + self::HOUR * $i + self::MINUTE, $type);
            // 结束订单后
            $suspend = $this->suspend($day + self::HOUR * $i + self::MINUTE, $type);
            // 查询请假人数, $type
            $date[$i . ":30"] = $countUser - $leaveUser - $orderUser - $suspend;
        }
        array_pop($date);
        return $this->success($date);
    }

    // 总员工人数
    public function countUser($type)
    {
        return Workers::where('project_ids', 'like', '%' . $type . '%')->count();
    }

    // 查询请假人数
    public function leaveUser($time, $type)
    {
        return LeaveLog::with('worker')
            ->whereHas('worker', function ($query) use ($type) {
                $query->where('project_ids', 'like', "%$type%");
            })
            ->where('begin_at', '<=', date('Y-m-d H:i', $time))
            ->where('end_at', '>=', date('Y-m-d H:i', $time + self::MINUTE))
            ->whereIn('status', self::STATUS)
            ->count();
    }

    // 查询订单人数
    public function orderUser()
    {
        $data = Order::whereIn('pay_type', [0, 1])
            ->get()->toArray();
        $worker = 0;
        foreach ($data as $key => $val) {
            $array = array_filter(explode(',', $val['sid']));
            $worker += count($array);
        }
        return $worker;
    }

    // 结束后2小时不派单
    public function suspend($time, $type)
    {
        return Order::with('workerUser')
            ->whereHas('workerUser', function ($query) use ($type) {
                $query->where('server_type', $type);
            })
            ->where('end_time', '<=', date('Y-m-d H:i', $time))
            ->where('end_time', '>=', date('Y-m-d H:i', $time - self::HOUR * 2))
            ->count();
    }
}
