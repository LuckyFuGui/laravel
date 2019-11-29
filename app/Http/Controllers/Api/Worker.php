<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\Workers;
use App\Http\Controllers\Controller;

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


        if(!$worker){
            return $this->error('没有检索到阿姨数据');
        }


        $score = \App\Model\Comment::query()->where('worker_id',$uid)->avg('score');


        $worker->score = $score ?? 5;

        $order = Order::query()->where('sid',$uid);
        $worker->orderCount = $order->where('pay_type',4)->count();
        $worker->waitOrderCount = $order->whereIn('pay_type',[0,1])->count();

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
        $order = Order::query()->with('order_project')
            ->where('sid','like','%'.$uid.'%')
            ->where(function ($q){
                $q->where('pay_type',0)->orWhere('pay_type',1);
            })
            ->where('created_at','>',$date)
            ->orderBy('id','desc')
            ->get()
            ->toArray();

        return $this->success($order);

    }
}
