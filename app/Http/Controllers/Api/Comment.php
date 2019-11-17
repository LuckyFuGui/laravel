<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class Comment extends Controller
{

    /**
     * 创建评论
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
    {

        $order_id = $request->order_id;

        if(!$order_id){
            return $this->error('缺少订单ID');
        }

        $order = Order::query()->with('order_comment','workerUser')->where('id',$order_id)->first();


        if(!$order){
            return $this->error('当前订单不存在');
        }

        if(!empty($order->order_comment)){
            return $this->error('当前订单已经评价过了');
        }


        if(!isset($request->is_later)){
            return $this->error('员工是否迟到？');
        }

        if(!isset($request->is_quiet)){
            return $this->error('当前服务是否安静？');
        }

        if(!isset($request->score)){
            return $this->error('请给当前服务打分');
        }

        if(!isset($request->attitude)){
            return $this->error('服务态度如何？');
        }

        DB::beginTransaction();
        try {
            \App\Model\Comment::query()->create(
                [
                    'order_id' => $order_id,
                    'worker_id' => $order->sid,
                    'is_later' => $request->is_later,
                    'is_quiet' => $request->is_quiet,
                    'score' => $request->score,
                    'attitude' => $request->attitude,
                    'remark' => $request->remark ?? ''
                ]
            );

            Order::query()->where('id', $order_id)->update(['pl' => 1]);
            DB::commit();
            return $this->success();
        }catch(\Exception $e){
            DB::rollBack();
            return $this->error('创建失败');
        }


    }
}
