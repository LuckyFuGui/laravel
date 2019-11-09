<?php

namespace App\Http\Controllers\Web;

use App\Model\Discount;
use App\Model\LeaveLog;
use App\Model\Order;
use App\Model\Workers;
use App\Model\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\UploadServices;
use PhpMyAdmin\MoTranslator\ReaderException;
use Illuminate\Support\Facades\DB;

class DiscountActivity extends Controller
{
	/**
	 * 优惠活动列表
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
	    $query = Discount::query();
        $data = $query
            ->offset(($request->page - 1) * $request->limit)
            ->limit($request->limit)
            ->get();

        $count = $query->count();
        return $this->successPage($data, $count);
    }

    /**
     * 新增代金券活动
     */
    public function add(Request $request)
    {
        $voucher_type = $request->voucher_type;
        $voucher_price = $request->voucher_price;
        $voucher_num = $request->voucher_num;
        $sale_price = $request->sale_price;
        $salable_num = $request->salable_num;
        $begin_at = $request->begin_at;
        $end_at = $request->end_at;
        $uid = $this->adminId;

        if(!in_array($voucher_type,[1,2,3,4,5])){
            return $this->error('代金劵类型不存在');
        }

        if(!is_numeric($voucher_price) || $voucher_price < 0){
            return $this->error('面值有误');
        }

        if(!is_numeric($sale_price) || $sale_price < 0){
            return $this->error('售价有误');
        }

        if(!is_numeric($voucher_num) || $voucher_num < 0){
            return $this->error('每组数量设置有误');
        }

        if(!is_numeric($salable_num) || $salable_num < 0){
            return $this->error('可售数量设置有误');
        }

        if(date('Y-m-d H:i:s',strtotime($begin_at)) != $begin_at){
            return $this->error('开始时间设置有误');
        }

        if(date('Y-m-d H:i:s',strtotime($end_at)) != $end_at){
            return $this->error('结束时间设置有误');
        }

        if(strtotime($begin_at) > strtotime($end_at)){
            return $this->error('开始时间不能大于结束时间');
        }

        if(time() < strtotime($begin_at)){
            $status = 0;
        }elseif(time() > strtotime($begin_at) && time() < strtotime($end_at)){
            $status = 1;
        }else{
            $status = 2;
        }

        Discount::query()->create([
            'status'=>$status,
            'type'=>1,//代金券
            'uid'=>$uid,
            'begin_at'=>$begin_at,
            'end_at'=>$end_at,
            'voucher_type'=>$voucher_type,
            'voucher_price'=>$voucher_price,
            'voucher_num'=>$voucher_num,
            'sale_price'=>$sale_price,
            'salable_num'=>$salable_num,
        ]);

        return $this->success();

    }

    /**
     * 编辑代金劵活动
     */
    public function edit(Request $request)
    {
        $id = $request->id;

        if(!$id){
            return $this->error('缺少ID');
        }

        $discount = Discount::query()->where('id',$id)->first();
        if(!$discount){
            return $this->error('缺少优惠卷信息');
        }

        $voucher_type = $request->voucher_type;
        $voucher_price = $request->voucher_price;
        $voucher_num = $request->voucher_num;
        $sale_price = $request->sale_price;
        $salable_num = $request->salable_num;
        $begin_at = $request->begin_at;
        $end_at = $request->end_at;

        if(!in_array($voucher_type,[1,2,3,4,5])){
            return $this->error('代金劵类型不存在');
        }

        if(!is_numeric($voucher_price) || $voucher_price < 0){
            return $this->error('面值有误');
        }

        if(!is_numeric($sale_price) || $sale_price < 0){
            return $this->error('售价有误');
        }

        if(!is_numeric($voucher_num) || $voucher_num < 0){
            return $this->error('每组数量设置有误');
        }

        if(!is_numeric($salable_num) || $salable_num < 0){
            return $this->error('可售数量设置有误');
        }

        if(date('Y-m-d H:i:s',strtotime($begin_at)) != $begin_at){
            return $this->error('开始时间设置有误');
        }

        if(date('Y-m-d H:i:s',strtotime($end_at)) != $end_at){
            return $this->error('结束时间设置有误');
        }

        if(strtotime($begin_at) > strtotime($end_at)){
            return $this->error('开始时间不能大于结束时间');
        }

        if(time() < strtotime($begin_at)){
            $status = 0;
        }elseif(time() > strtotime($begin_at) && time() < strtotime($end_at)){
            $status = 1;
        }else{
            $status = 2;
        }

        $discount->voucher_type = $voucher_type;
        $discount->voucher_price = $voucher_price;
        $discount->sale_price = $sale_price;
        $discount->voucher_num = $voucher_num;
        $discount->salable_num = $salable_num;
        $discount->begin_at  = $begin_at;
        $discount->end_at  = $end_at;
        $discount->save();

        return $this->success();

    }

    /**
     * 取消优惠卷
     */
    public function cancel(Request $request)
    {
        $id = $request->id;
        if(!$id){
            return $this->error('缺少ID');
        }

        $discount = Discount::query()->where('id',$id)->first();
        if(!$discount){
            return $this->error('优惠活动不存在');
        }

        if(!in_array($discount->status,[0,1])){
            return $this->error('当前优惠活动已结束或者已取消');
        }

        $discount->status = 3;
        $discount->save();
        return $this->success();
    }
}
