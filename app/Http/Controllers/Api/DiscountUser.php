<?php

namespace App\Http\Controllers\Api;

use App\Model\Discount;
use App\Model\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\DiscountUser as UserDiscount;

class DiscountUser extends Controller
{
	/**
	 * 获取当前用户可用优惠券列表
	 */
    public function getDiscountByUser(Request $request)
    {

        $data = $request->date;
        if(date('Y-m-d H:i:s',strtotime($data)) != $data){
            return $this->error('时间格式有误');
        }
        $uid = $this->user->id ?? '';
        if(!$uid){
            return $this->error('获取用户ID失败');
        }

        //优惠券有效期为购买时间延后一年
        $data = UserDiscount::query()->with('discount')
            ->where('uid',$uid)
            ->where('status',0)
            ->where('effective_date','>',now())
            ->get()
            ->toArray();

        return $this->success($data);
    }

    /**
     * 优惠活动列表
     */
    public function getDiscount(Request $request)
    {
        if(!isset($request->page)){
            $request->page = 1;
        }else{
            isset($request->page) && $request->page < 1 ? 1 : $request->page;
        }

        $query = Discount::query()->where('end_at','>',date('Y-m-d H:i:s'))
            ->whereIn('status',[0,1]);
        $count = $query->count();
        $data = $query
            ->offset(($request->page - 1) * $request->limit)
            ->limit($request->limit)
            ->get();

        return $this->successPage($data,$count);
    }

    /**
     * 我的卡券
     */
    public function userCoupon(Request $request)
    {
        if(!isset($request->page)){
            $request->page = 1;
        }else{
            isset($request->page) && $request->page < 1 ? 1 : $request->page;
        }

        $uid = $this->user->id ?? '';
        if(!$uid){
            return $this->error('获取用户ID失败');
        }

        $query = UserDiscount::query()->with('discount')->where('uid',$uid);
        $data = $query
            ->offset(($request->page - 1) * $request->limit)
            ->limit($request->limit)
            ->get();

        $count = $query->count();
        return $this->successPage($data, $count);

    }

    /**
     * 个人中心
     */
    public function userCenter()
    {
        $uid = $this->user->id ?? '';
        if(!$uid){
            return $this->error('获取用户ID失败');
        }
        $user = User::query()->where('id',$uid)->first();
        $user->conpun = \App\Model\DiscountUser::query()->where('uid',$uid)->count();
        return $this->success($user);
    }






}
