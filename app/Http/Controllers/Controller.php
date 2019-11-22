<?php

namespace App\Http\Controllers;

use App\Model\User;
use App\Model\Order;
use App\Model\Admins;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // 计费
    const PRICE = 5;
    // 一小时
    const HOUR = 3600;
    // 半小时
    const MINUTE = 1800;
    // 上架、支付
    const TYPE = 1;
    // 下架、未支付
    const NOTYPE = 0;
    // 请假异常
    const STATUS = [1];
    // 异常订单
    const ORDERTYPE = [0, 1];
    // 后端id
    protected $adminId = 0;
    // 用户数据
    protected $user = [];

    public function __construct(Request $Request)
    {
        // 后台
        if ($Request->header('token')) {
            $this->adminId = Admins::where('token', $Request->header('token'))->value('id');
        }
        // 前台
        if ($Request->header('openid')) {
            $this->user = User::where('openid', $Request->header('openid'))->first();
        }
        // 清除订单
        $orderData = Order::where('pay_type', 0)->where('created_at', '<=', date('Y-m-d H:i:s', time() - 1800))->get();
        if ($orderData) {
            foreach ($orderData as $key => $value) {
                Order::where('id', $value['id'])->update(['pay_type' => 3]);
            }
        }
    }

    /**
     * 返回结果
     */
    public function success($data = [], $code = 200, $msg = '成功')
    {
        return [
            'code' => $code,
            'data' => $data,
            'msg' => $msg
        ];
    }

    /**
     * 返回分页结果
     */
    public function successPage($data = [], $page = 0, $code = 200, $msg = '成功')
    {
        return [
            'code' => $code,
            'data' => $data,
            'msg' => $msg,
            'page' => $page
        ];
    }

    /**
     * 返回异常
     */
    public function error($data = [], $code = 500, $msg = '失败')
    {
        return [
            'code' => $code,
            'data' => $data,
            'msg' => $msg
        ];
    }

    /**
     * 删除图片
     */
    public function unlink($url)
    {
        $path = public_path() . $url;
        @unlink($path);
        return true;
    }

    /**
     * 页码
     */
    public function newPage($page)
    {
        return !empty($page) ? $page : 1;
    }

    /**
     * 条数
     */
    public function newLimit($limit)
    {
        return !empty($limit) ? $limit : 10;
    }
}
