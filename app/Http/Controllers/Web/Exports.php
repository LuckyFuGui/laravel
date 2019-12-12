<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Exports extends Controller
{

    public function index(Request $request)
    {


        OrderExports::order_export($request);

        /*switch ($request->export_id){
            case 'order_export':
        }*/


        return $this->success('导出任务添加成功');
    }


}