<?php

namespace App\Http\Controllers\Api;

use App\Model\Discount;
use App\Model\Order;
use App\Model\User;
use App\Model\Workers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\DiscountUser as UserDiscount;

class Worker extends Controller
{
    /**
     * 个人中心
     */
    public function workerCenter()
    {
        $uid = $this->user->id ?? '';
        if (!$uid) {
            return $this->error('获取用户ID失败');
        }
        $worker = Workers::query()->where('uid', $uid)->first(['img','name']);
        $worker->score = \App\Model\Comment::query()->where('worker_id',$uid)->avg('score');
        $order = Order::query()->where('sid',$uid)->where('pay_type',4);
        $worker->orderCount = $order->count();
        $worker->price = $order->sum('payment');
        return $this->success($worker);
    }

    /**
     * 获取24小时内待完成订单
     */
    public function workerOrders()
    {
        $uid = $this->user->id ?? '';
        if (!$uid) {
            return $this->error('获取用户ID失败');
        }

        $date = date('Y-m-d H:i:s',strtotime('-24 hour'));
        $order = Order::query()
            ->where('sid',$uid)
            ->whereIn('pay_type',[0,1])
            ->where('created_at','>',$date)
            ->get()
            ->toArray();

        return $this->success($order);

    }
}
