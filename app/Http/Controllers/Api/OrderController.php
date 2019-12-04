<?php

namespace App\Http\Controllers\Api;

use App\Model\Discount;
use App\Model\DiscountPurchaseRecord;
use App\Model\Order;
use App\Model\PayLog;
use App\Model\Address;
use App\Model\Workers;
use App\Model\Project;
use App\Model\LeaveLog;
use App\Model\Wasteland;
use App\Model\OrderError;
use App\Model\OrderProject;
use App\Model\DiscountUser;
use App\Model\DailyCleaning;
use App\Model\AdditionalServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    /**
     *
     * 日常保洁
     *
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store1(Request $request)
    {
        $resOrder = $this->orderServer();
        if ($resOrder) return $this->error('有待支付订单');
        // 必传参数
        $this->validate($request, [
            'aid' => 'required',
            'start_time' => 'required',
            'project_ids' => 'required',
            'countPrice' => 'required',
            'end_time' => 'required',
        ]);
        $userNum = $request->num;
        $userNum = !empty($userNum) ? $userNum : 1;
        $sid = $request->sid;
        $sidProject = !empty($sid) ? $sid : 1;
        // 开始日期当天时间戳
        $time = strtotime(date('Y-m-d', $request->start_time));
        // 地址
        $address = Address::select('name', 'phone', 'address', 'comment')->find($request->aid);
        // 插入数据
        $data['uid'] = $this->user['id'];
        $data['name'] = $address['name'];
        $data['phone'] = $address['phone'];
        $data['address'] = $address['address'];
        $data['comment'] = $address['comment'];
        // 点单号
        $data['order_sn'] = 'wx' . date('YmdHis') . rand(10000, 99999);
        // 查询数据的条件
        $in = [];
        $info = [];
        $dataProject = json_decode($request->project_ids);
        foreach ($dataProject as $k => $v) {
            $in[] = intval($k);
            $info[$k] = $v;
        }
        $request->project_ids = $info;
        // 项目查询
        $project = AdditionalServices::where('services_status', self::TYPE)
            ->whereIn('id', $in)
            ->get();
        if (!$project) return $this->error();
        // 开始和结束时间
        $endtime = $request->end_time % (self::MINUTE / 60);
        if ($endtime) {
            $request->end_time = $request->start_time + (30 - $endtime) * 60;
        } else {
            $request->end_time = $request->start_time + $request->end_time * 60;
        }
        $data['start_time'] = date('Y-m-d H:i', $request->start_time);
        $data['end_time'] = date('Y-m-d H:i', $request->end_time);
        // 优惠卷
        $coupon = 0;
        if ($request->cid) {
            $data['cid'] = $request->cid;
            $coupon = DiscountUser::find($request->cid)->value('voucher_price');
        } else {
            $data['cid'] = 0;
        }
        $data['coupon'] = $coupon;
        // 特殊时间服务
        $data['special'] = 0;
        $times = $time + self::HOUR * 19 + self::MINUTE;
        if ($times < $request->end_time) {
            if ($times <= $request->start_time) {
                $data['special'] = ceil(($request->end_time - $request->start_time) / 1800) * self::PRICE;
            } else {
                $data['special'] = ceil(($request->end_time - $times) / 1800) * self::PRICE;
            }
        }
        // 服务类型
        $data['server_type'] = 1;
        // 匹配数据
        $sid = $this->serverId(1, $time);
        if (count($sid) < $userNum) return $this->error('暂无服务人员');
        $sidStr = '';
        $sidServer = [];
        foreach ($sid as $key => $val) {
            if (count($sidServer) < $userNum) {
                $sidStr .= $sid[$key] . ',';
                $sidServer[$key] = $val;
            }
        }
        $data['sid'] = $sidStr;
        // 创建事务
        DB::beginTransaction();
        try {
            // 添加获取id
            $oid = Order::create($data);
            // 更新价格，加入详情单
            $priceQuery = DailyCleaning::where('id', $sidProject)->value('price');
            $price = 0;
            $OrderProject['pid'] = $sidProject;
            $OrderProject['oid'] = $oid['id'];
            $OrderProject['price'] = $priceQuery;
            $OrderProject['name'] = '日常保洁';
            $OrderProject['num'] = 1;
            OrderProject::create($OrderProject);
            foreach ($project as $key => $value) {
                $OrderProject['pid'] = $value['id'];
                $OrderProject['oid'] = $oid['id'];
                $OrderProject['price'] = $value['services_price'];
                $OrderProject['name'] = $value['services_name'];
                $OrderProject['num'] = $request->project_ids[$value['id']];
                $rester = OrderProject::create($OrderProject);
                // 计算总价格
                if ($rester) {
                    $price = $price + $value['services_price'] * $request->project_ids[$value['id']];
                }
            }
            $price = $price + $priceQuery + $data['special'] - $coupon;
            if ($price == $request->countPrice) {
                // 修改订单表
                $orderInstall = Order::find($oid['id'])->update(['time_price' => 0, 'payment' => $price, 'pay_type' => self::NOTYPE,]);
                // 是否添加成功，成功返回数据
                if ($orderInstall) {
                    DB::commit();
                    return $this->success(['orderId' => $oid['id']]);
                } else {
                    DB::rollBack();
                    return $this->error('修改数据失败');
                }
            } else {
                DB::rollBack();
                return $this->error('价格产生差异:' . $price);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('插入异常');
        }
    }

    /**
     *
     * 家电、普通处理
     *
     * [store description]
     * @param Request $request [description]
     * @return [type]           [description]
     */
    public function store23(Request $request)
    {
        $resOrder = $this->orderServer();
        if ($resOrder) return $this->error('有待支付订单');
        // 必传参数
        $this->validate($request, [
            'aid' => 'required',
            'server_type' => 'required',
            'start_time' => 'required',
            'project_ids' => 'required',
            'countPrice' => 'required',
            'end_time' => 'required',
        ]);
        $userNum = $request->num;
        $userNum = !empty($userNum) ? $userNum : 1;
        // 开始日期当天时间戳
        $time = strtotime(date('Y-m-d', $request->start_time));
        // 地址
        $address = Address::select('name', 'phone', 'address', 'comment')->find($request->aid);
        // 插入数据
        $data['uid'] = $this->user['id'];
        $data['name'] = $address['name'];
        $data['phone'] = $address['phone'];
        $data['address'] = $address['address'];
        $data['comment'] = $address['comment'];
        // 点单号
        $data['order_sn'] = 'wx' . date('YmdHis') . rand(10000, 99999);
        // 查询数据的条件
        $in = [];
        $info = [];
        $dataProject = json_decode($request->project_ids);
        foreach ($dataProject as $k => $v) {
            $in[] = intval($k);
            $info[$k] = $v;
        }
        $request->project_ids = $info;
        // 项目查询
        $project = Project::where('type', $request->server_type)
            ->where('state', self::TYPE)
            ->whereIn('id', $in)
            ->get();
        if (!$project) return $this->error();
        // 开始和结束时间
        $endtime = $request->end_time % (self::MINUTE / 60);
        if ($endtime) {
            $request->end_time = $request->start_time + (30 - $endtime) * 60;
        } else {
            $request->end_time = $request->start_time + $request->end_time * 60;
        }
        $data['start_time'] = date('Y-m-d H:i', $request->start_time);
        $data['end_time'] = date('Y-m-d H:i', $request->end_time);
        // 优惠卷
        $coupon = 0;
        if ($request->cid) {
            $data['cid'] = $request->cid;
            $coupon = DiscountUser::find($request->cid)->value('voucher_price');
        } else {
            $data['cid'] = 0;
        }
        $data['coupon'] = $coupon;
        // 特殊时间服务
        $data['special'] = 0;
        $times = $time + self::HOUR * 19 + self::MINUTE;
        if ($times < $request->end_time) {
            if ($times <= $request->start_time) {
                $data['special'] = ceil(($request->end_time - $request->start_time) / 1800) * self::PRICE;
            } else {
                $data['special'] = ceil(($request->end_time - $times) / 1800) * self::PRICE;
            }
        }
        // 服务类型
        $data['server_type'] = $request->server_type + 1;
        // 匹配数据
        $sid = $this->serverId($request->server_type + 1, $time);
        if (count($sid) < $userNum) return $this->error('暂无服务人员');
        $sidStr = '';
        $sidServer = [];
        foreach ($sid as $key => $val) {
            if (count($sidServer) < $userNum) {
                $sidStr .= $sid[$key] . ',';
                $sidServer[$key] = $val;
            }
        }
        $data['sid'] = $sidStr;
        // 创建事务
        DB::beginTransaction();
        try {
            // 添加获取id
            $oid = Order::create($data);
            // 更新价格，加入详情单
            $price = 0;
            foreach ($project as $key => $value) {
                $OrderProject['pid'] = $value['id'];
                $OrderProject['oid'] = $oid['id'];
                $OrderProject['price'] = $value['price'];
                $OrderProject['name'] = $value['serverName'];
                $OrderProject['num'] = $request->project_ids[$value['id']];
                $rester = OrderProject::create($OrderProject);
                // 计算总价格
                if ($rester) {
                    $price = $price + $value['price'] * $request->project_ids[$value['id']];
                }
            }
            $price = $price + $data['special'] - $coupon;
            if ($price == $request->countPrice) {
                // 修改订单表
                $orderInstall = Order::find($oid['id'])->update(['time_price' => 0, 'payment' => $price, 'pay_type' => self::NOTYPE,]);
                // 是否添加成功，成功返回数据
                if ($orderInstall) {
                    DB::commit();
                    return $this->success(['orderId' => $oid['id']]);
                } else {
                    DB::rollBack();
                    return $this->error('修改数据失败:' . $price);
                }
            } else {
                DB::rollBack();
                return $this->error('价格产生差异:' . $price);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('插入异常');
        }
    }

    /**
     *
     * 新居开荒
     *
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store4(Request $request)
    {
        $resOrder = $this->orderServer();
        if ($resOrder) return $this->error('有待支付订单');
        // 必传参数
        $this->validate($request, [
            'aid' => 'required',
            'start_time' => 'required',
            'countPrice' => 'required',
            'end_time' => 'required',
        ]);
        $newTime = $request->end_time;
        $userNum = $request->num;
        $userNum = !empty($userNum) ? $userNum : 1;
        $sid = $request->sid;
        $sidProject = !empty($sid) ? $sid : 1;
        // 开始日期当天时间戳
        $time = strtotime(date('Y-m-d', $request->start_time));
        // 地址
        $address = Address::select('name', 'phone', 'address', 'comment')->find($request->aid);
        // 插入数据
        $data['uid'] = $this->user['id'];
        $data['name'] = $address['name'];
        $data['phone'] = $address['phone'];
        $data['address'] = $address['address'];
        $data['comment'] = $address['comment'];
        // 点单号
        $data['order_sn'] = 'wx' . date('YmdHis') . rand(10000, 99999);
        // 开始和结束时间
        $endtime = $request->end_time % 30;
        if ($endtime) {
            $request->end_time = $request->start_time + (30 - $endtime) * 60;
        } else {
            $request->end_time = $request->start_time + $request->end_time * 60;
        }
        $data['start_time'] = date('Y-m-d H:i', $request->start_time);
        $data['end_time'] = date('Y-m-d H:i', $request->end_time);
        // 优惠卷
        $coupon = 0;
        if ($request->cid) {
            $data['cid'] = $request->cid;
            $coupon = DiscountUser::find($request->cid)->value('voucher_price');
        } else {
            $data['cid'] = 0;
        }
        $data['coupon'] = $coupon;
        // 特殊时间服务
        $data['special'] = 0;
        $times = $time + self::HOUR * 19 + self::MINUTE;
        if ($times < $request->end_time) {
            if ($times <= $request->start_time) {
                $data['special'] = ceil(($request->end_time - $request->start_time) / 1800) * self::PRICE;
            } else {
                $data['special'] = ceil(($request->end_time - $times) / 1800) * self::PRICE;
            }
        }
        // 服务类型
        $data['server_type'] = 4;
        // 匹配数据
        $sid = $this->serverId(4, $time);
        if (count($sid) < $userNum) return $this->error('暂无服务人员');
        $sidStr = '';
        $sidServer = [];
        foreach ($sid as $key => $val) {
            if (count($sidServer) < $userNum) {
                $sidStr .= $sid[$key] . ',';
                $sidServer[$key] = $val;
            }
        }
        $data['sid'] = $sidStr;
        // 创建事务
        DB::beginTransaction();
        try {
            // 添加获取id
            $oid = Order::create($data);
            // 总价格
            $priceQuery = Wasteland::find($sidProject)->first();
            // 所有人1小时
            $workerAll = $priceQuery['basics_price'] + ($userNum - 1) * $priceQuery['increase_price'];
            // 全程服务
            $workerPrice = $workerAll * ceil($newTime / 60);
            // 特殊服务
            $workerSpecial = $data['special'] * $userNum;
            $price = $workerPrice + $workerSpecial - $coupon;
            // 更新加入详情单
            $OrderProject['pid'] = 0;
            $OrderProject['oid'] = $oid['id'];
            $OrderProject['price'] = ($priceQuery['basics_price'] + ($userNum - 1) * $priceQuery['increase_price']) * $userNum;
            $OrderProject['name'] = "新居开荒";
            $OrderProject['num'] = $userNum;
            OrderProject::create($OrderProject);
            // 计算总价格
            if ($price == $request->countPrice) {
                // 修改订单表
                $orderInstall = Order::find($oid['id'])->update(['time_price' => 0, 'payment' => $price, 'pay_type' => self::NOTYPE,]);
                // 是否添加成功，成功返回数据
                if ($orderInstall) {
                    DB::commit();
                    return $this->success(['orderId' => $oid['id']]);
                } else {
                    DB::rollBack();
                    return $this->error('修改数据失败');
                }
            } else {
                DB::rollBack();
                return $this->error('价格产生差异' . $price);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('插入异常');
        }
    }

    /**
     * 12小时内下单+钱
     * @param $createTime
     * @param $startTime
     */
    public function sunPrice($createTime, $startTime)
    {
        $time = $startTime - $createTime;
        if ($time <= 43200) return 10;
        return 0;
    }

    /**
     * 找数据
     * [serverId description]
     * @param  [type] $type     [description]
     * @param  [type] $end_time [description]
     * @return [type]           [description]
     */
    public function serverId($type, $end_time)
    {
        // 时间转换
        $end_time = date('Y-m-d', $end_time);
        // 符合的数据
        $wid = Workers::where('project_ids', 'like', '%' . $type . '%')
            ->where('status', 1)
            ->pluck('id')->toArray();
        // 正在订单的数据
        $oid = Order::whereIn('pay_type', self::ORDERTYPE)
            ->pluck('sid')->toArray();
        $oidWorker = [];
        foreach ($oid as $key => $val) {
            $oidServer = array_filter(explode(',', $val));
            $oidWorker = array_merge($oidWorker, $oidServer);
        }
        $oid = $oidWorker;
        // 请假的数据
        $leave = LeaveLog::with('worker')
            ->whereHas('worker', function ($query) use ($type) {
                $query->where('project_ids', 'like', "%$type%");
            })
            ->where('begin_at', '<=', $end_time)
            ->where('end_at', '>=', $end_time)
            ->whereIn('status', self::ORDERTYPE)
            ->pluck('worker_id')->toArray();
        // 准备过滤的数据
        $oids = [];
        foreach ($oid as $key => $value) {
            if (strpos($value, ',')) {
                $oids = array_merge($oids, explode(',', $value));
            } else {
                $oids[] = $value;
            }
        }
        $all = array_unique(array_merge($leave, $oids));
        // 等待中的数据
        return array_diff($wid, $all);
    }

    /**
     * 查询数据
     *
     * @param Request $request
     *
     * @return array
     * @author  高鹏贵 <gaopenggui@vchangyi.com>
     * @date    2019/11/14 16:08
     */
    public function index(Request $request)
    {
        // 数据
        $where = $request->where;
        $user = $request->user;
        $page = $this->newPage($request->page);
        $limit = $this->newLimit($request->limit);
        // 总数量
        if ($user == 'sid') {
            $count = Order::where($user, 'like', "%" . $this->user['id'] . "%");
        } else {
            $count = Order::where($user, $this->user['id']);
        }
        if ($where) $count = $count->where('pay_type', $where);
        $count = $count->count();
        $data = Order::with(['order_project', 'order_comment']);
        if ($user == 'sid') {
            $data = $data->where($user, 'like', "%" . $this->user['id'] . "%");
        } else {
            $data = $data->where($user, $this->user['id']);
        }
        if ($where) $data = $data->where('pay_type', $where);
        $data = $data->offset(($page - 1) * $limit)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()->toArray();
        foreach ($data as &$value) {
            if (!empty($value['order_comment']['score'])) {
                $value['order_comments'] = $value['order_comment']['score'];
            } else {
                $value['order_comments'] = 0;
            }
            unset($value['order_comment']);
        }
        return $this->successPage($data, $count);
    }

    /**
     *
     * 详情
     * @param Request $request
     *
     * @author  高鹏贵 <gaopenggui@vchangyi.com>
     * @date    2019/11/14 17:03
     */
    public function onlyIndex(Request $request)
    {
        $data = Order::with(['order_project', 'order_comment'])
            ->where('id', $request->id)
            ->first()->toArray();
        if (!empty($data['order_comment']['score'])) {
            $data['order_comments'] = $data['order_comment']['score'];
        } else {
            $data['order_comments'] = 0;
        }
        $data['voucher_type'] = null;
        $data['voucher_price'] = null;
        if ($data['cid'] > 0) {
            $coupon = DiscountUser::find($data['cid'])->first();
            $data['voucher_type'] = $coupon['voucher_type'];
            $data['voucher_price'] = $coupon['voucher_price'];
        }
        unset($data['order_comment']);
        return $this->success($data);
    }

    /**
     * 取消
     * @param Request $request
     */
    public function cancel(Request $request)
    {
        if (!$request->id) return $this->error('缺少订单id');
        $data = [
            'oid' => $request->id,
            'content' => $request->info,
            'type' => 1,
            'uid' => $this->user['id'],
        ];
        $payType = Order::where('id', $request->id)->first();
        DB::beginTransaction();
        try {
            Order::where('id', $request->id)->update(['pay_type' => 2]);
            OrderError::create($data);
            if ($payType['pay_type'] == 1) {
                DiscountUser::where('id', $payType['cid'])->update(['status' => 0]);
            }
            DB::commit();
            return $this->success();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('取消订单失败');
        }
    }

    /**
     * 完成
     * @param Request $request
     */
    public function overOrder(Request $request)
    {
        if (!$request->id) return $this->error('缺少订单id');
        DB::beginTransaction();
        try {
            Order::where('id', $request->id)->update(['pay_type' => 4]);
            DB::commit();
            return $this->success();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error();
        }
    }

    /**
     * 成功
     * @param Request $request
     */
    public function succOrder(Request $request)
    {
        $post = $_REQUEST;
        if ($post == null) {
            $post = file_get_contents("php://input");
        }
        libxml_disable_entity_loader(true); //禁止引用外部xml实体
        $xml = simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA);//XML转数组
        $post_data = (array)$xml;
        $pay = PayLog::create([
            'appid' => $post_data['appid'],
            'attach' => $post_data['attach'],
            'bank_type' => $post_data['bank_type'],
            'cash_fee' => $post_data['cash_fee'],
            'fee_type' => $post_data['fee_type'],
            'id' => $post_data['id'],
            'is_subscribe' => $post_data['is_subscribe'],
            'mch_id' => $post_data['mch_id'],
            'nonce_str' => $post_data['nonce_str'],
            'openid' => $post_data['openid'],
            'out_trade_no' => $post_data['out_trade_no'],
            'result_code' => $post_data['result_code'],
            'return_code' => $post_data['return_code'],
            'sign' => $post_data['sign'],
            'time_end' => $post_data['time_end'],
            'total_fee' => $post_data['total_fee'],
            'trade_type' => $post_data['trade_type'],
            'transaction_id' => $post_data['transaction_id'],
        ]);

        info($pay);

        if (strpos($post_data['out_trade_no'], 'con') !== false) {
            DB::beginTransaction();
            try {
                $recode = DiscountPurchaseRecord::query()->where('id', $post_data['id'])->first();
                $recode->pay_status = 1;
                $recode->wx_sn = $post_data['transaction_id'];
                $recode->pay_at = now();
                $recode->pay_price = $post_data['total_fee'] / 100;
                $recode->save();

                DiscountUser::query()->where('pay_sn', $post_data['out_trade_no'])->update([
                    'pay_status' => 1,
                    'effective_date' => date('Y-m-d H:i:s', strtotime('+1 year'))
                ]);

                //$discount = Discount::query()->where('id',$recode->discount_id)->first();
                //$discount->salable_num = $discount->salable_num - 1;
                //$discount->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                info('优惠券状态变更失败');
                info($e->getMessage());
            }

        } else {
            $orderUpdate = Order::where('id', $post_data['id'])->update(['pay_type' => 1]);
            info($orderUpdate);
            $cid = Order::where('id', $post_data['id'])->value('cid');
            info($cid);
            $dis = [
                'status' => 1,
                'use_at' => date('Y-m-d H:i:s')
            ];
            $DiscountUser = DiscountUser::where('id', $cid)->update($dis);
            info($DiscountUser);
        }

        $str = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        echo $str;
    }

    /**
     * 是否有订单
     * @return mixed
     */
    public function orderServer()
    {
        return Order::where('uid', $this->user['id'])->where('pay_type', 0)->get()->toArray();
    }
}
