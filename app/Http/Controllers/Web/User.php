<?php

namespace App\Http\Controllers\Web;

use App\Model\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class User extends Controller
{
    /**
     * 用户管理
     */
    public function index(Request $request)
    {
        if(!isset($request->page)){
            $request->page = 1;
        }else{
            isset($request->page) && $request->page < 1 ? 1 : $request->page;
        }


        if(!isset($request->limit)){
            $request->limit = 20;
        }else{
            isset($request->limit) && $request->limit > 20 ? 20 : $request->limit;
        }

        $query = \App\Model\User::query();
        $data = $query
            ->offset(($request->page - 1) * $request->limit)
            ->limit($request->limit)
            ->orderBy('id','desc')
            ->get();

        foreach ($data as $key=>$val){
            $order = Order::query()->where('uid',$val->id)->where('pay_type',4)->get();
            $val->count = $order->count();
            $val->payment = $order->sum('payment');
            $val->discount = $order->sum('coupon');
        }
        $count = \App\Model\User::query()->count();
        return $this->successPage($data, $count);
    }

    /**
     * 修改用户状态
     * @param Request $request
     * @return array
     */
    public function updateStatus(Request $request)
    {
        if(!isset($request->uid)){
            return $this->error('缺少用户ID');
        }

        if(!isset($request->status)){
            return $this->error('当前状态缺失');
        }

        $user = \App\Model\User::query()->where('id',$request->uid)->first();

        if(!$user){
            return $this->error('当前用户不存在');
        }

        if(!in_array($request->status,[0,1])){
            return $this->error('状态不存在');
        }

        /*if($request->status == 1){
            $user->status = 0;
        }else{
            $user->status = 1;
        }*/
        $user->status = $request->status;
        $user->save();

        return $this->success();
    }


}
