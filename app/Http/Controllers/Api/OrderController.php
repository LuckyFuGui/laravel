<?php

namespace App\Http\Controllers\Api;

use App\Model\Order;
use App\Model\Address;
use App\Model\Workers;
use App\Model\Project;
use App\Model\LeaveLog;
use App\Model\Wasteland;
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
     * 家电、普通处理
     *
     * [store description]
     * @param Request $request [description]
     * @return [type]           [description]
     */
    public function store23(Request $request)
    {
        // 必传参数
        $this->validate($request, [
            'aid' => 'required',
            'server_type' => 'required',
            'start_time' => 'required',
            'project_ids' => 'required',
            'countPrice' => 'required',
            'end_time' => 'required',
        ]);
        $resOrder = $this->orderServer();
        if ($resOrder) $this->error('有待支付订单');
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
            $coupon = DiscountUser::find($request->cid)->value('voucher_price');
        } else {
            $data['cid'] = 0;
        }
        $data['coupon'] = $coupon;
        // 特殊时间服务
        $data['special'] = 0;
        $times = $time + self::HOUR * 19 + self::MINUTE;
        if ($times < $request->end_time) {
            $data['special'] = ceil(ceil(($request->end_time - $times) / 60) / 30) * self::PRICE;
        }
        // 服务类型
        $data['server_type'] = $request->server_type + 1;
        // 匹配数据
        $sid = $this->serverId($request->server_type + 1, $time);
        if (count($sid) <= $userNum) return $this->error('暂无服务人员');
        $sidStr = '';
        for ($i = 0; $i < $userNum; $i++) {
            $sidStr .= $sid[$i] . ',';
        }
        $data['sid'] = $sidStr;
        // 创建事务
        DB::beginTransaction();
        try {
            // 添加获取id
            $oid = Order::create($data);
            // 更新价格，加入详情单
            $price = 0;
            $prices = $data['coupon'];
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
            $price = $price - $prices + $data['special'];
            if ($price == $request->countPrice) {
                // 修改订单表
                $orderInstall = Order::find($oid['id'])->update(['payment' => $price, 'pay_type' => self::NOTYPE,]);
                // 是否添加成功，成功返回数据
                if ($orderInstall) {
                    $dis = [
                        'status' => 1,
                        'use_at' => date('Y-m-d H:i:s')
                    ];
                    DiscountUser::where('id', $request->cid)->update($dis);
                    DB::commit();
                    return $this->success($price);
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
     * 日常保洁
     *
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store1(Request $request)
    {
        $resOrder = $this->orderServer();
        if ($resOrder) $this->error('有待支付订单');
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
            $in[] = $k;
            $info[$k] = $v;
        }
        $request->project_ids = $info;
        // 项目查询
        $project = AdditionalServices::where('project_id', 1)
            ->where('services_status', self::TYPE)
            ->whereIn('id', $in)
            ->get();
        if (!$project) return $this->error();
        // 开始和结束时间
        $endtime = $request->end_time % (self::MINUTE / 60);
        if ($endtime) {
            $request->end_time = $request->start_time + (30 - $endtime) * 60;
        } else {
            $request->end_time = $request->start_time + $request->end_time;
        }
        $data['start_time'] = date('Y-m-d H:i', $request->start_time);
        $data['end_time'] = date('Y-m-d H:i', $request->end_time);
        // 优惠卷
        $coupon = 0;
        if ($request->cid) {
            $coupon = DiscountUser::find($request->cid)->value('voucher_price');
        } else {
            $data['cid'] = 0;
        }
        $data['coupon'] = $coupon;
        // 特殊时间服务
        $data['special'] = 0;
        $times = $time + self::HOUR * 19 + self::MINUTE;
        if ($times < $request->end_time) {
            $data['special'] = ceil(ceil(($request->end_time - $times) / 60) / 30) * self::PRICE;
        }
        // 服务类型
        $data['server_type'] = 1;
        // 匹配数据
        $sid = $this->serverId(1, $time);
        if (count($sid) <= $userNum) return $this->error('暂无服务人员');
        $sidStr = '';
        for ($i = 0; $i < $userNum; $i++) {
            $sidStr .= $sid[$i] . ',';
        }
        $data['sid'] = $sidStr;
        // 创建事务
        DB::beginTransaction();
        try {
            // 添加获取id
            $oid = Order::create($data);
            // 更新价格，加入详情单
            $price = 0;
            $prices = $data['special'];
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
            $priceQuery = DailyCleaning::find($request->sid)->value('price');
            $price = $price - $prices + $priceQuery;
            if ($price == $request->countPrice) {
                // 修改订单表
                $orderInstall = Order::find($oid['id'])->update(['payment' => $price, 'pay_type' => self::NOTYPE,]);
                // 是否添加成功，成功返回数据
                if ($orderInstall) {
                    $dis = [
                        'status' => 1,
                        'use_at' => date('Y-m-d H:i:s')
                    ];
                    DiscountUser::where('id', $request->cid)->update($dis);
                    DB::commit();
                    return $this->success();
                } else {
                    DB::rollBack();
                    return $this->error('修改数据失败');
                }
            } else {
                DB::rollBack();
                return $this->error('价格产生差异');
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
        if ($resOrder) $this->error('有待支付订单');
        // 必传参数
        $this->validate($request, [
            'aid' => 'required',
            'start_time' => 'required',
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
        // 开始和结束时间
        $endtime = $request->end_time % (self::MINUTE / 60);
        if ($endtime) {
            $request->end_time = $request->start_time + (30 - $endtime) * 60;
        } else {
            $request->end_time = $request->start_time + $request->end_time;
        }
        $data['start_time'] = date('Y-m-d H:i', $request->start_time);
        $data['end_time'] = date('Y-m-d H:i', $request->end_time);
        // 优惠卷
        $coupon = 0;
        if ($request->cid) {
            $coupon = DiscountUser::find($request->cid)->value('voucher_price');
        } else {
            $data['cid'] = 0;
        }
        $data['coupon'] = $coupon;
        // 特殊时间服务
        $data['special'] = 0;
        $times = $time + self::HOUR * 19 + self::MINUTE;
        if ($times < $request->end_time) {
            $data['special'] = ceil(ceil(($request->end_time - $times) / 60) / 30) * self::PRICE;
        }
        // 服务类型
        $data['server_type'] = 4;
        // 匹配数据
        $sid = $this->serverId(4, $time);
        if (count($sid) <= $userNum) return $this->error('暂无服务人员');
        $sidStr = '';
        for ($i = 0; $i < $userNum; $i++) {
            $sidStr .= $sid[$i] . ',';
        }
        $data['sid'] = $sidStr;
        // 创建事务
        DB::beginTransaction();
        try {
            // 添加获取id
            $oid = Order::create($data);
            // 更新加入详情单
            $OrderProject['pid'] = 0;
            $OrderProject['oid'] = $oid['id'];
            $OrderProject['price'] = self::PRICE_MEM + ($userNum - 1) * 40;
            $OrderProject['name'] = "新居开荒";
            $OrderProject['num'] = $userNum;
            OrderProject::create($OrderProject);
            // 计算总价格
            $priceQuery = Wasteland::find($request->sid)->first();
            $price = ($priceQuery['basics_price'] + ($userNum - 1) * $priceQuery['increase_price']) * $userNum - $data['special'];
            if ($price == $request->countPrice) {
                // 修改订单表
                $orderInstall = Order::find($oid['id'])->update(['payment' => $price, 'pay_type' => self::NOTYPE,]);
                // 是否添加成功，成功返回数据
                if ($orderInstall) {
                    $dis = [
                        'status' => 1,
                        'use_at' => date('Y-m-d H:i:s')
                    ];
                    DiscountUser::where('id', $request->cid)->update($dis);
                    DB::commit();
                    return $this->success();
                } else {
                    DB::rollBack();
                    return $this->error('修改数据失败');
                }
            } else {
                DB::rollBack();
                return $this->error('价格产生差异');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('插入异常');
        }
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
            ->where('start_time', '<=', $end_time)
            ->pluck('sid')->toArray();
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
        $count = Order::where($user, $this->user['id']);
        if ($where) $count = $count->where('pay_type', $where);
        $count = $count->count();
        $data = Order::with(['order_project', 'order_comment'])
            ->where($user, $this->user['id']);
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
            ->where('uid', $this->user['id'])->first()->toArray();
        if (!empty($data['order_comment']['score'])) {
            $data['order_comments'] = $data['order_comment']['score'];
        } else {
            $data['order_comments'] = 0;
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
        $res = Order::where('id', $request->id)->update(['pay_type' => 2]);
        if ($res) return $this->success();
        return $this->error();
    }

    /**
     * 成功
     * @param Request $request
     */
    public function succOrder(Request $request)
    {
        $res = Order::where('id', $request->id)->update(['pay_type' => 4]);
        if ($res) return $this->success();
        return $this->error();
    }

    public function orderServer()
    {
        return Order::where('uid', $this->user['id'])->where('pay_type', 0)->get();
    }
}
