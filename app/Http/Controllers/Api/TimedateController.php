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
            // 总人数
            $userCount = $countUser;
            // 查询请假人数
            $leaveUser = $this->leaveUser($day + self::HOUR * $i, $type);
            // 订单人数
            $orderUser = $this->orderUser($day + self::HOUR * $i);
            // 合并去重，算总数
            if (is_array($orderUser)) {
                $userCountServer = [];
                foreach ($userCount as $key => $val) {
                    if (!in_array($val, $orderUser)) {
                        $userCountServer[] = $val;
                    }
                }
            } else {
                $userCountServer = $userCount;
            }
            $count = count($userCountServer);
            // 现在剩余人数
            $num = $count - $leaveUser;
            $date[$i . ":00"] = $num > 0 ? $num : 0;
            // *****************
            // *** 半小时处理 ***
            // *****************
            // 查询请假人数
            $leaveUser = $this->leaveUser($day + self::HOUR * $i + self::MINUTE, $type);
            // 订单人数
            $orderUser = $this->orderUser($day + self::HOUR * $i + self::MINUTE);
            // 合并去重，算总数
            if (is_array($orderUser)) {
                $userCountServer = [];
                foreach ($userCount as $key => $val) {
                    if (!in_array($val, $orderUser)) {
                        $userCountServer[] = $val;
                    }
                }
            } else {
                $userCountServer = $userCount;
            }
            $count = count($userCountServer);
            // 现在剩余人数
            $num = $count - $leaveUser;
            $date[$i . ":30"] = $num > 0 ? $num : 0;
        }
        array_pop($date);
        return $this->success($date);
    }

    // 总员工人数
    public function countUser($type)
    {
        return Workers::where('status', self::TYPE)->where('project_ids', 'like', '%' . $type . '%')->pluck('id')->toArray();
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
    public function orderUser($time)
    {
        $data = Order::whereIn('pay_type', [0, 1])
            // 去的路上2小时
            ->where('start_time', '<=', date('Y-m-d H:i', $time + self::HOUR * 2))
            // 回来的路上2小时
            ->where('end_time', '>=', date('Y-m-d H:i', $time - self::HOUR * 2))
            ->get()->toArray();
        $array = [];
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $array = array_filter(explode(',', $val['sid']));
            }
        }
        return $array;
    }
}
