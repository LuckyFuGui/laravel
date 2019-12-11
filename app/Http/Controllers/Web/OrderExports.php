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
            ->get();
        foreach ($data as $key => &$value) {
            $sid = array_filter(explode(',', $value['sid']));
            $value['worker'] = Workers::select('name', 'phone')->whereIn('id', $sid)->get()->toArray();
        }

        self::export($data);
    }

    static function export($items)
    {
        $data = [];
        foreach ($items as $item) {

            if($item->pay_type == 0){
                $status = '待付款';
            }elseif ($item->pay_type == 1){
                if(date('Y-m-d H:i:s') > $item->start_time){
                    $status = '待服务';
                }else{
                    $status = '服务中';
                }
            }elseif ($item->pay_type == 2){
                $status = '已取消';
            }elseif($item->pay_type == 4){
                $status = '已完成';
            }


            if($item->server_type == 1){
                $server_type = '日常保洁';
            }elseif ($item->server_type == 2){
                $server_type = '电器清洁';
            }elseif ($item->server_type == 3){
                $server_type = '全家除螨';
            }else{
                $server_type = '新居开荒';
            }


            $sid = explode(',',$item->sid);
            $name = Workers::query()->whereIn('id',$sid)->pluck('name')->toArray();

            $name = trim(implode(',',$name),',');

            $phone = Workers::query()->whereIn('id',$sid)->pluck('phone')->toArray();
            $phone = trim(implode(',',$phone),',');

            $data[] = [
                '订单类型' => '服务订单',
                '订单状态' => $status,
                '订单号' => $item->order_sn,
                '下单项目' => $server_type,
                '联系人' => $item->user->name ?? '',
                '联系电话' => $item->user->phone,
                '支付方式' => '微信',
                '订单价格' => $item->coupon + $item->payment,
                '支付现金' => $item->payment,
                '退款金额' => $item->retreat,
                '工作人员姓名' =>$name,
                '工作人员手机' => $phone,
            ];
        }

        $filename = time();
        $storePath = public_path() . '/orders/' . date('Ymd');
        Excel::create($filename, function (LaravelExcelWriter $excel) use ($data) {
            $excel->sheet('导出数据', function (LaravelExcelWorksheet $sheet) use ($data) {
                $sheet->fromArray($data);
            });
        })->store('xlsx', $storePath, true);
    }



}
