<?php

namespace App\Http\Controllers\Api;

use App\Model\Discount;
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


        $data = UserDiscount::query()->with('discount')->where('uid',$uid)->where('status',0)->
            whereHas('discount',function($q) use ($data){
                $q->where('end_at','>',$data)->where('begin_at','<',$data);

        })->get()->toArray();

        return $this->success($data);
    }

    /**
     * 优惠活动列表
     */
    public function getDiscount()
    {
        $data = Discount::query()->where('end_at','>',date('Y-m-d H:i:s'))->whereIn('status',[0,1])->get()->toArray();
        return $this->success($data);
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






}
