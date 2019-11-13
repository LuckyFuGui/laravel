<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TimeController extends Controller
{
    const HIST = 3600;
    const MIST = 1800;
    public function orderTime(Request $request)
    {
        // 指定时间
        $day = $request->day;
        // 获取当前存在的数据
        // 员工表总人数-请假-开始预约人
        // 时间段
        $date = [];
        for ($i = 7; $i <= 22; $i++) {
            $date[$i . ':00'] = $day + self::HIST  * $i;
            // 查询用户人数
            $date[$i . ':30'] = $day + self::HIST  * $i + self::MIST;
        }
        array_pop($date);
        return $this->success($date);
    }
}
