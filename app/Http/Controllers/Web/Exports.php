<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Exports extends Controller
{

    public function index(Request $request)
    {

        if(is_null(json_decode($request))){
            return $this->error('json数据格式不正确');
        }

        $arr = json_decode($request);
        if(!$arr['export_id']){
            return $this->error('请选择导出类型');
        }

        switch ($request->export_id){
            case 'order_export':
                OrderExports::order_export($arr);
        }


        return $this->success('导出任务添加成功');
    }


}