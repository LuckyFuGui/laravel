<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Exports extends Controller
{

    public function index(Request $request)
    {
        if(!$request->export_id){
            return $this->error('请选择导出类型');
        }

        switch ($request->export_id){
            case 'order_export':
                if(is_null(json_decode($request->json))){
                    return $this->error('json数据格式不正确');
                }
                OrderExports::order_export($request->json);
        }


        return $this->success('导出任务添加成功');
    }


}