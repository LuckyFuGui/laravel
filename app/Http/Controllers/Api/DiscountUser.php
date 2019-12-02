<?php

namespace App\Http\Controllers\Api;

use App\Model\Discount;
use App\Model\DiscountPurchaseRecord;
use App\Model\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\DiscountUser as UserDiscount;
use Illuminate\Support\Facades\DB;


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


        $data = UserDiscount::query()->with('discount')
            ->where('uid',$uid)
            ->where('status',0)
            ->where('pay_status',1)
            ->whereHas('discount',function($q) use ($data){
                $q->where('end_at','>',$data)->where('begin_at','<',$data);
            })
            ->orderBy('id','desc')
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

        $query = Discount::query()
            ->where('end_at','>',date('Y-m-d H:i:s'))
            ->where('begin_at','<',date('Y-m-d H:i:s'))
            ->where('salable_num','>',0)
            ->whereIn('status',[0,1]);

        $count = $query->count();

        $data = $query
            ->offset(($request->page - 1) * $request->limit)
            ->limit($request->limit)
            ->orderBy('id','desc')
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
            ->orderBy('id','desc')
            ->get();

        $count = UserDiscount::query()->with('discount')->where('uid',$uid)->count();
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

    /**
     * 购买优惠券
     */
    public function buyCoupon(Request $request)
    {
        $discount_id = $request->discount_id;
        if(!$discount_id){
            return $this->error('缺失优惠券ID');
        }
        $uid = $this->user->id ?? '';
        if(!$uid){
            return $this->error('获取用户ID失败');
        }

        $discount = Discount::query()->where('id',$discount_id)->first();
        if(!$discount){
            return $this->error('当前优惠券信息不存在');
        }

        if($discount->begin_at > date('Y-m-d H:i:s') || $discount->end_at < date('Y-m-d H:i:s')){
            return $this->error('当前优惠活动不在有效期内');
        }

        if($discount->salable_num <= 0){
            return $this->error('当前优惠券已售罄');
        }

        //先生成基本数据，订单编号
        $purchase = [
            'uid'=>$uid,
            'discount_id'=>$discount_id,
            'voucher_type'=>$discount->voucher_type,
            'voucher_price'=>$discount->voucher_price,
            'voucher_num'=>$discount->voucher_num,
            'sale_price'=>$discount->sale_price,
            'pay_status'=>0,//todo 先写死
            'pay_price'=>$discount->sale_price,//todo 先按照优惠券金额来写
            'pay_sn'=>'con' . date('YmdHis') . rand(10000, 99999),
        ];


        DB::beginTransaction();

        try{
            $recode = DiscountPurchaseRecord::query()->create($purchase);
            for ($i = 0; $i < $discount->voucher_num; $i++) {
                $dis_user = [
                    'uid'=>$uid,
                    'discount_id'=>$discount_id,
                    'voucher_type'=>$discount->voucher_type,
                    'voucher_price'=>$discount->voucher_price,
                    'pay_sn'=>$purchase['pay_sn'],
                    'pay_status'=>0,//todo 先写死
                    //todo 购买成功之后按照购买时间重新计算有效期
                    'effective_date'=>date('Y-m-d H:i:s',strtotime('+1 year')),
                ];
                \App\Model\DiscountUser::query()->create($dis_user);

                //$discount->salable_num = $discount->salable_num - 1;
                //$discount->save();
            }

            DB::commit();
            return $this->success([
                'orderId'=>$recode->id,
                'type'=>2
            ]);
        }catch(\Exception $e){
            DB::rollBack();
            return $this->error('购买失败');
        }


        //todo 这里跳转到微信支付，先创建好记录数据，支付回调成功改支付状态
        //todo 回调之后修改 两张表的支付状态，并且写入支付时间，修改优惠券有效期,以及支付金额,并且修改优惠券数量

    }






}
