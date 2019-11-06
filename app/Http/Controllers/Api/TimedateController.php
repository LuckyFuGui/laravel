<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimedateController extends Controller
{
	// 计费
	const PRICE = 5;
	// 一小时
	const HOUR = 3600;
	// 半小时
	const MINUTE = 1800;
	// 时间列表
    public function index(Request $request)
    {
    	// 具体天数
    	$day = $request->day;
    	// 返回时间
    	$date = [];
    	// 时间计算
    	for ($i=7; $i < 18; $i++) { 
    		$date[$i] = $day + self::HOUR * $i;
    		$date[$i] = $day + self::HOUR * $i + self::MINUTE;
    	}
    	return $this->success($date);
    }
}
