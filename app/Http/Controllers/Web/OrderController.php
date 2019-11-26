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
        $where = $request->where;
        $whereName = $request->whereName;
        $page = $this->newPage($request->page);
        $limit = $this->newLimit($request->limit);
        $data = Order::query()->with('order_project')
            ->where($whereName, $where)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy('id','desc')
            ->get()
            ->toArray();
        $count = Order::where($whereName, $where)->count();
        return $this->successPage($data, $count);
    }
}
