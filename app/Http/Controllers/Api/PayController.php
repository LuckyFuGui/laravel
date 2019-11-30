<?php

namespace App\Http\Controllers\Api;

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
        $path = app_path() . '/WxPay/';
        require_once $path . "lib/WxPay.Api.php";
        require_once $path . "example/WxPay.JsApiPay.php";
        require_once $path . "example/WxPay.Config.php";
        require_once $path . 'example/log.php';
        //初始化日志
        $logHandler = new CLogFileHandler($path . "logs/" . date('Y-m-d') . '.log');
        $log = Log::Init($logHandler, 15);
//        //打印输出数组信息
//        function printf_info($data)
//        {
//            foreach($data as $key=>$value){
//                echo "<font color='#00ff55;'>$key</font> :  ".htmlspecialchars($value, ENT_QUOTES)." <br/>";
//            }
//        }

//①、获取用户openid
        try {

            $tools = new JsApiPay();
            $openId = 'o7JX-shGaLPJwy2PWSQWFhSk2Ak4';//$request->header('openid');//$tools->GetOpenid();

            //②、统一下单
            $input = new WxPayUnifiedOrder();
            $input->SetBody("test");
            $input->SetAttach("test");
            $input->SetOut_trade_no("sdkphp" . date("YmdHis"));
            $input->SetTotal_fee("1");
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag("test");
            $input->SetNotify_url("http://cqdaguanjia.com/api/order/notify");
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid($openId);
            $config = new WxPayConfig();
            $order = WxPayApi::unifiedOrder($config, $input);
            $jsApiParameters = $tools->GetJsApiParameters($order);
            //获取共享收货地址js函数参数
            $editAddress = $tools->GetEditAddressParameters();
            return $this->success($jsApiParameters);
        } catch (Exception $e) {
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
