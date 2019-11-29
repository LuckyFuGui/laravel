<?php

namespace App\Http\Controllers\Web;

use App\Model\Order;
use App\Model\OrderProject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
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
        $where = [];
        if (!empty($request->pay_type)) $where['pay_type'] = $request->pay_type;
        if (!empty($request->server_type)) $where['server_type'] = $request->server_type;
        if (!empty($request->phone)) $where['phone'] = $request->phone;
        if (!empty($request->start_time)) $start_time = $request->start_time;
        if (!empty($request->end_time)) $end_time = $request->end_time;
        $page = $this->newPage($request->page);
        $limit = $this->newLimit($request->limit);
        $data = Order::query()->with('order_project');
        if (!empty($where)) $data = $data->where($where);
        if (!empty($start_time)) $data = $data->where('start_time','<=', $start_time);
        if (!empty($end_time)) $data = $data->where('end_time','>=', $end_time);
        $data = $data->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        $count = Order::where($where)->count();
        return $this->successPage($data, $count);
    }
}
