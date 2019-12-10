<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Model\Workers;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;


class OrderExports extends Controller
{

    static function order_export($request)
    {

        if($request){
            if (isset($request['pay_type'])) $where['pay_type'] = $request['pay_type'];
            if (isset($request['server_type'])) $where['server_type'] = $request['server_type'];
            if (isset($request['phone'])) $where['phone'] = $request['phone'];
            if (isset($request['start_time'])) $start_time = $request['start_time'];
            if (isset($request['end_time'])) $end_time = $request['end_time'];
        }
        $data = \App\Model\Order::query()->with('order_project');
        if (!empty($where)) $data = $data->where($where);
        if (!empty($start_time)) $data = $data->where('start_time', '>=', $start_time);
        if (!empty($end_time)) $data = $data->where('end_time', '<=', $end_time);
        $data = $data
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        foreach ($data as $key => &$value) {
            $sid = array_filter(explode(',', $value['sid']));
            $value['worker'] = Workers::select('name', 'phone')->whereIn('id', $sid)->get()->toArray();
        }

        self::export($data);
    }

//    static function export($items)
//    {
//        $data = [];
//        foreach ($items as $item) {
//            $data[] = [
//                '订单类型' => '服务订单',
//                '订单状态' => '待服务',
//                '订单号' => '订单号',
//                '下单项目' => '下单项目',
//                '联系人' => '联系人',
//                '联系电话' => '联系电话',
//                '支付方式' => '支付方式',
//                '订单价格' => 111,
//                '支付现金' => 100,
//                '退款金额' => 0,
//                '工作人员姓名' => 'ceshi',
//                '工作人员手机' => 18855556666,
//            ];
//        }
//
//        $filename = time();
//        $storePath = public_path() . '/orders/' . date('Ymd');
//        Excel::create($filename, function (LaravelExcelWriter $excel) use ($data) {
//            $excel->sheet('导出数据', function (LaravelExcelWorksheet $sheet) use ($data) {
//                $sheet->fromArray($data);
//            });
//        })->store('xlsx', $storePath, true);
//    }


}
