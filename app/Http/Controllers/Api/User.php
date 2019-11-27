<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\Workers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class User extends Controller
{
    /**
     * 获取用户数据
     */
    public function getUserInfo(Request $request)
    {

        $uid = $request->openid;
        if (!$uid) {
            return $this->error('缺失openid');
        }
        $user = \App\Model\User::query()->where('openid', $uid)->first();

        return $this->success($user);
    }



}
