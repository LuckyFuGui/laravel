<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use Log;
use JsApiPay;
use WxPayApi;
use WxPayConfig;
use CLogFileHandler;
use WxPayUnifiedOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PayController extends Controller
{
    /*
     * 商户号
     */
    protected $num = '1563083931';
    /*
     * 密钥
     */
    protected $key = 'C4CA4238A0B923820DCC509A6F75849B';

    /**
     * @param Request $request
     */
    public function jsapi(Request $request)
    {
        $id = $request->input('orderId');
        $type = $request->input('type');
        if (!$id || !$type) return $this->error('缺少参数');
        switch ($type) {
            case 1:
                $data = Order::where('id', $id)->first()->toArray();
                switch ($data['server_type']) {
                    case 1:
                        $orderName = '日常保洁';
                        break;
                    case 2:
                        $orderName = '家电清洗';
                        break;
                    case 3:
                        $orderName = '专业除螨';
                        break;
                    case 4:
                        $orderName = '新居开荒';
                        break;
                }
                $orderNum = $data['order_sn'];
                $orderPrice = $data['payment'] * 100;
                break;
            case 2:
                $data = Order::where('id', $id)->first()->toArray();
                $orderName = '优惠卷';
                $orderNum = $data['order_sn'];
                $orderPrice = $data['payment'] * 100;
                break;
            default:
                return $this->error('类型不存在');
        }
//        $orderName = '大管家';
//        $orderNum = date('YmdHis');
//        $orderPrice = '1';
        $path = app_path() . '/WxPay/';
        require_once $path . "lib/WxPay.Api.php";
        require_once $path . "example/WxPay.JsApiPay.php";
        require_once $path . "example/WxPay.Config.php";
        require_once $path . 'example/log.php';
        //初始化日志
        $logHandler = new CLogFileHandler($path . "logs/" . date('Y-m-d') . '.log');
        $log = Log::Init($logHandler, 15);
        //①、获取用户openid
        try {

            $tools = new JsApiPay();
            $openId = $request->header('openid');//$tools->GetOpenid();

            //②、统一下单
            $input = new WxPayUnifiedOrder();
            $input->SetBody($orderName);
            $input->SetAttach($orderName);
            $input->SetOut_trade_no($orderNum);// 订单号
//            $input->SetTotal_fee($orderPrice);//金额
            $input->SetTotal_fee('1');//金额
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag($orderName);
            $input->SetNotify_url("http://cqdaguanjia.com/api/order/succOrder?id=" . $id);//异步回调
            $input->SetTrade_type("JSAPI");//支付类型
            $input->SetOpenid($openId);
            $config = new WxPayConfig();
            $order = WxPayApi::unifiedOrder($config, $input);
            $jsApiParameters = $tools->GetJsApiParameters($order);
            //获取共享收货地址js函数参数
            //$editAddress = $tools->GetEditAddressParameters();
            return $this->success($jsApiParameters);
        } catch (\Exception $e) {
            Log::ERROR(json_encode($e));
            return $this->error();
        }
        //③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
        /**
         * 注意：
         * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
         * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
         * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
         */

    }
}
