<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\OrderExports as order;


class Exports extends Controller
{

    public function index(Request $request)
    {

        if(!$request->export_id){
            return $this->error('请选择导出类型');
        }

        switch ($request->export_id){
            case 'order_export':
                OrderExports::order_export($request->where);
        }


        return $this->success('导出任务添加成功');
    }


}
