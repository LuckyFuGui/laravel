<?php

namespace App\Http\Controllers\Api;

use App\Model\DiscountPurchaseRecord;
use App\Model\DiscountUser;
use App\Model\Order;
use App\Model\PayLog;
use Log;
use JsApiPay;
use WxPayApi;
use WxPayRefund;
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
     * 支付
     * @param Request $request
     * @return array
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
                $orderPrice = 1;// $data['payment'] * 100;
                break;
            case 2:
                $data = DiscountPurchaseRecord::query()->where('id', $id)->first();
                if (!$data) {
                    return $this->error('当前订单不存在');
                }
                $orderName = '优惠卷';
                $orderNum = $data->pay_sn;
                $orderPrice = 1;// $data->sale_price * 100;
                break;
            default:
                return $this->error('类型不存在');
        }
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
            $input->SetTotal_fee($orderPrice);//金额
//            $input->SetTotal_fee('1');//金额
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag($orderName);
            $input->SetNotify_url("http://cqdaguanjia.com/api/order/succOrder?id=$id");//异步回调
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
    }

    /**
     * 退款
     * @param Request $request
     * transaction_id：微信订单号
     * out_trade_no：商户订单号
     * total_fee：订单总金额
     * refund_fee：退款金额
     */
    public function retreat(Request $request)
    {
        $id = $request->input('orderId');
        if (!$id) return $this->error('缺少订单id参数');
        $pay = PayLog::where('id', $id)->where('attach', '!=', '优惠卷')->first();
        if ($pay) {
            $info = $pay->toArray();
            $cid = Order::where('id', $id)->value('cid');
            $path = app_path() . '/WxPay/';
            require_once $path . "lib/WxPay.Api.php";
            require_once $path . 'example/log.php';
            require_once $path . "example/WxPay.Config.php";
            //初始化日志
            $logHandler = new CLogFileHandler($path . "logs/" . date('Y-m-d') . '.log');
            $log = Log::Init($logHandler, 15);
            // 退款
            if (isset($info["transaction_id"]) && $info["transaction_id"] != "") {
                try {
                    $transaction_id = $info["transaction_id"];
                    $total_fee = $info["total_fee"];
                    $refund_fee = $info["total_fee"];// $_REQUEST["refund_fee"];
                    $input = new WxPayRefund();
                    $input->SetTransaction_id($transaction_id);
                    $input->SetTotal_fee($total_fee);
                    $input->SetRefund_fee($refund_fee);

                    $config = new WxPayConfig();
                    $input->SetOut_refund_no("sdkphp" . date("YmdHis"));
                    $input->SetOp_user_id($config->GetMerchantId());
                    $payData = WxPayApi::refund($config, $input);
                    if ($payData['return_code'] == 'SUCCESS') {
                        // 修改订单数据
                        $res = Order::where('id', $id)->update(['pay_type' => 2]);
                        $dis = [
                            'status' => 0,
                            'use_at' => date('Y-m-d H:i:s')
                        ];
                        if ($cid) DiscountUser::where('id', $cid)->update($dis);
                        if ($res) return $this->success();
                        // 返回数据
                        info('退款成功，订单未修改：' . $info["transaction_id"]);
                        return $this->success();
                    }
                } catch (\Exception $e) {
                    info('退款失败：' . $info["transaction_id"]);
                    Log::ERROR(json_encode($e));
                }
                exit();
            }
        } else {
            return $this->error('数据不真实');
        }
    }
}
