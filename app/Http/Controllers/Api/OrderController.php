<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\Address;
use App\Model\OrderProject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
	/**
     * 添加
     * [store description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function store(Request $request)
    {
    	$address = Address::select('name','phone','address','comment')->find($request->aid);
    	if (!$address) return $this->error();
    	$address['start_time'] = $request->start_time;
    	$address['end_time'] = $request->end_time;
    	$coupon = 0;
    	if ($request->cid) {
    		// $coupon = Address::find($request->cid)->value('coupon');
    	}
    	$address['coupon'] = $coupon;
    	dd($address);
    	return $this->success($address);
        $res = Address::create($request->all());
        if ($res) return $this->success();
        return $this->error();
    }
}
