<?php

namespace App\Http\Controllers\Web;

use App\Model\DiscountPurchaseRecord;
use App\Model\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function index()
    {
    	return $this->success([],200,'成功');
    	// return $this->error();
    }

    /**
     * 后台首页数据
     */
    public function show()
    {

        //--------------昨天数据---------------
        $last_price1 =  Order::query()
            ->where('created_at','>=',date('Y-m-d 00:00:00',strtotime('-1 day')))
            ->where('created_at','<',date('Y-m-d 23:59:59',strtotime('-1 day')))
            ->whereIn('pay_type',[1,4])
            ->sum('payment');

        //todo 这里需要获取到当天退款需要加收的手续费
        $last_return_fee = 0;

        //服务收款金额
        $last_price1 = $last_price1 + $last_return_fee;

        //当日购买的优惠券金额
        $last_discount = DiscountPurchaseRecord::query()
            ->where('pay_at','>',date('Y-m-d 00:00:00',strtotime('-1 day')))
            ->where('pay_at','<',date('Y-m-d 23:59:59',strtotime('-1 day')))
            ->where('pay_status',1)
            ->sum('pay_price');

        //购物收款金额
        $last_price2 = $last_price1 + $last_discount;

        //运营消耗金额
        $last_price3 = Order::query()
            ->where('created_at','>=',date('Y-m-d 00:00:00',strtotime('-1 day')))
            ->where('created_at','<',date('Y-m-d 23:59:59',strtotime('-1 day')))
            ->whereIn('pay_type',[1,4])
            ->sum('coupon');

        //todo 退款金额
        $last_return = 0;

        //成交客户数
        $last_num = Order::query()
            ->where('created_at','>=',date('Y-m-d 00:00:00',strtotime('-1 day')))
            ->where('created_at','<',date('Y-m-d 23:59:59',strtotime('-1 day')))
            ->where('pay_type',4)
            ->get()
            ->groupBy('uid')
            ->count();

        //新增会员数
        $last_user_num = \App\Model\User::query()
            ->where('created_at','>=',date('Y-m-d 00:00:00',strtotime('-1 day')))
            ->where('created_at','<',date('Y-m-d 23:59:59',strtotime('-1 day')))
            ->count();

        if($last_num == 0){
            $res = 0;
        }else{
            $res = $last_price2 / $last_num;
        }
        $last_price4 = $last_price2 + $res;

        //-------------昨天数据------------------


        //--------------今天数据-----------------------
        $price1 =  Order::query()
            ->where('created_at','>=',date('Y-m-d 00:00:00'))
            ->where('created_at','<',date('Y-m-d 23:59:59'))
            ->whereIn('pay_type',[1,4])
            ->sum('payment');

        //todo 这里需要获取到当天退款需要加收的手续费
        $return_fee = 0;

        //服务收款金额
        $price1 = $price1 + $return_fee;

        //当日购买的优惠券金额
        $discount = DiscountPurchaseRecord::query()
            ->where('pay_at','>',date('Y-m-d 00:00:00'))
            ->where('pay_at','<',date('Y-m-d 23:59:59'))
            ->where('pay_status',1)
            ->sum('pay_price');

        //购物收款金额
        $price2 = $price1 + $discount;

        //运营消耗金额
        $price3 = Order::query()
            ->where('created_at','>=',date('Y-m-d 00:00:00'))
            ->where('created_at','<',date('Y-m-d 23:59:59'))
            ->whereIn('pay_type',[1,4])
            ->sum('coupon');

        //todo 退款金额
        $return = 0;

        //成交客户数
        $num = Order::query()
            ->where('created_at','>=',date('Y-m-d 00:00:00'))
            ->where('created_at','<',date('Y-m-d 23:59:59'))
            ->where('pay_type',4)
            ->get()
            ->groupBy('uid')
            ->count();

        //新增会员数
        $user_num = \App\Model\User::query()
            ->where('created_at','>=',date('Y-m-d 00:00:00'))
            ->where('created_at','<',date('Y-m-d 23:59:59'))
            ->count();

        if($num == 0){
            $res = 0;
        }else{
            $res = $price2 / $num;
        }
        $price4 = $price1 + $res;

        //--------------今天数据-----------------------


        $data = [
            'last_price1' => $last_price1,
            'last_price2' => $last_price2,
            'last_price3' => $last_price3,
            'last_return' => $last_return,
            'last_num' => $last_num,
            'last_user_num' =>$last_user_num,
            'last_price4' => $last_price4,

            'price1' => $price1,
            'price2' => $price2,
            'price3' => $price3,
            'return' => $return,
            'num' => $num,
            'user_num' =>$user_num,
            'price4' => $price4,
        ];

        return $this->success($data);

    }

    /**
     * 月数据
     */
    public function showMonth()
    {
        //--------------上月数据---------------
        $last_price1 =  Order::query()
            ->where('created_at','>=',date('Y-m-01 00:00:00',strtotime('-1 month')))
            ->where('created_at','<',date('Y-m-d 23:59:59', strtotime(date('Y-m-01') . ' -1 day')))
            ->whereIn('pay_type',[1,4])
            ->sum('payment');

        //todo 这里需要获取到上月退款需要加收的手续费
        $last_return_fee = 0;

        //服务收款金额
        $last_price1 = $last_price1 + $last_return_fee;

        //上月购买的优惠券金额
        $last_discount = DiscountPurchaseRecord::query()
            ->where('pay_at','>',date('Y-m-01 00:00:00',strtotime('-1 month')))
            ->where('pay_at','<',date('Y-m-d 23:59:59', strtotime(date('Y-m-01') . ' -1 day')))
            ->where('pay_status',1)
            ->sum('pay_price');

        //购物收款金额
        $last_price2 = $last_price1 + $last_discount;

        //运营消耗金额
        $last_price3 = Order::query()
            ->where('created_at','>=',date('Y-m-01 00:00:00',strtotime('-1 month')))
            ->where('created_at','<',date('Y-m-d 23:59:59', strtotime(date('Y-m-01') . ' -1 day')))
            ->whereIn('pay_type',[1,4])
            ->sum('coupon');

        //todo 退款金额
        $last_return = 0;

        //成交客户数
        $last_num = Order::query()
            ->where('created_at','>=',date('Y-m-01 00:00:00',strtotime('-1 month')))
            ->where('created_at','<',date('Y-m-d 23:59:59', strtotime(date('Y-m-01') . ' -1 day')))
            ->where('pay_type',4)
            ->get()
            ->groupBy('uid')
            ->count();

        //新增会员数
        $last_user_num = \App\Model\User::query()
            ->where('created_at','>=',date('Y-m-01 00:00:00',strtotime('-1 month')))
            ->where('created_at','<',date('Y-m-d 23:59:59', strtotime(date('Y-m-01') . ' -1 day')))
            ->count();

        if($last_num == 0){
            $res = 0;
        }else{
            $res = $last_price2 / $last_num;
        }
        $last_price4 = $last_price2 + $res;

        //-------------上月数据------------------


        //--------------本月数据-----------------------
        $price1 =  Order::query()
            ->where('created_at','>=',date('Y-m-01 00:00:00'))
            ->whereIn('pay_type',[1,4])
            ->sum('payment');

        //todo 这里需要获取到本月退款需要加收的手续费
        $return_fee = 0;

        //服务收款金额
        $price1 = $price1 + $return_fee;

        //本月购买的优惠券金额
        $discount = DiscountPurchaseRecord::query()
            ->where('pay_at','>',date('Y-m-01 00:00:00'))
            ->where('pay_status',1)
            ->sum('pay_price');

        //购物收款金额
        $price2 = $price1 + $discount;

        //运营消耗金额
        $price3 = Order::query()
            ->where('created_at','>=',date('Y-m-01 00:00:00'))
            ->whereIn('pay_type',[1,4])
            ->sum('coupon');

        //todo 退款金额
        $return = 0;

        //成交客户数
        $num = Order::query()
            ->where('created_at','>=',date('Y-m-01 00:00:00'))
            ->where('pay_type',4)
            ->get()
            ->groupBy('uid')
            ->count();

        //新增会员数
        $user_num = \App\Model\User::query()
            ->where('created_at','>=',date('Y-m-01 00:00:00'))
            ->count();

        if($num == 0){
            $res = 0;
        }else{
            $res = $price2 / $num;
        }
        $price4 = $price1 + $res;

        //--------------本月数据-----------------------


        $data = [
            'last_price1' => $last_price1,
            'last_price2' => $last_price2,
            'last_price3' => $last_price3,
            'last_return' => $last_return,
            'last_num' => $last_num,
            'last_user_num' =>$last_user_num,
            'last_price4' => $last_price4,

            'price1' => $price1,
            'price2' => $price2,
            'price3' => $price3,
            'return' => $return,
            'num' => $num,
            'user_num' =>$user_num,
            'price4' => $price4,
        ];

        return $this->success($data);
    }
}
