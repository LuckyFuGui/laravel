<?php

namespace App\Http\Controllers\Web;

use App\Http\Api\Order;
use App\Model\AdditionalServices;
use App\Model\DailyCleaning;
use App\Model\ProjectImg;
use App\Model\Wasteland;
use App\Model\Workers;
use App\Model\User;
use function GuzzleHttp\Promise\is_settled;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\UploadServices;
use Illuminate\Support\Facades\DB;
use PhpMyAdmin\MoTranslator\ReaderException;

class Projects extends Controller
{

    /**
     * 项目首页管理
     */
    public function index(Request $request)
    {
        $type_id = $request->type_id ?? 1;
        if(!in_array($type_id,[1,2,3,4])){
            return $this->error('type_id 参数错误');
        }

        $month = date('Y-m-01 00:00:00');
        //本月收款金额
        $price1 = \App\Model\Order::query()
            ->where('server_type',$type_id)
            ->where('created_at','>',$month)
            ->whereIn('pay_type',[1,4])
            ->sum('payment');

        //累计收款金额
        $price2 = \App\Model\Order::query()
            ->where('server_type',$type_id)
            ->whereIn('pay_type',[1,4])
            ->sum('payment');


        //本月优惠券金额
        $coupon1 = \App\Model\Order::query()
            ->where('server_type',$type_id)
            ->where('created_at','>',$month)
            ->whereIn('pay_type',[1,4])
            ->sum('coupon');



        //本月订单金额
        $price3 = $price1+$coupon1;

        //累计优惠券金额
        $coupon2= \App\Model\Order::query()
            ->where('server_type',$type_id)
            ->whereIn('pay_type',[1,4])
            ->sum('coupon');

        //累计订单金额
        $price4 = $price2+$coupon2;

        //本月退款金额
        $price5 = \App\Model\Order::query()
            ->where('server_type',$type_id)
            ->where('updated_at','>',$month)
            ->where('pay_type',2)
            ->sum('retreat');

        //累计退款金额
        $price6 = \App\Model\Order::query()
            ->where('server_type',$type_id)
            ->where('pay_type',2)
            ->sum('retreat');



        //本月运营消耗金额
        $price7 = $coupon1;

        //累计运营消耗金额
        $price8 = $coupon2;

        //下单客户总数
        $count= \App\Model\Order::query()
            ->where('server_type',$type_id)
            ->where(function($q){
                $q->where('pay_type',1)
                    ->orWhere('pay_type',4);
            })
            ->groupBy('uid')
            ->count();

        //下单总人数
        $all = \App\Model\Order::query()
            ->where('server_type',$type_id)
            ->where(function($q){
                $q->where('pay_type',1)
                    ->orWhere('pay_type',4);
            })
            ->count();


        //客单价
        if($all == 0){
            $price9 = 0;
        }else{
            $price9 = number_format($price2 / $all,2);
        }


        return $this->success([
            'price1'=>$price1,
            'price2'=>$price2,
            'price3'=>$price3,
            'price4'=>$price4,
            'price5'=>$price5,
            'price6'=>$price6,
            'price7'=>$price7,
            'price8'=>$price8,
            'count'=>$count,
            'price9'=>$price9,

        ]);
    }

    /**
     * 日常保洁 基础数据
     */
    public function daliy()
    {
        $services = AdditionalServices::query()->where('project_id',1)->get()->toArray();
        $data = DailyCleaning::query()->get()->toArray();
        $arr['services'] = $services;
        $arr['main'] = $data;
        return $this->success($arr);
    }

    /**
     * 获取新居开荒项目数据
     */
    public function wasteland()
    {
        $services = Wasteland::query()->first();
        if(!$services){
            $services = Wasteland::query()->create([
                'basics_price'=>80,
                'increase_price'=>30
            ]);
        }

        return $this->success($services);
    }

    /**
     * 获取项目详情
     */
    public function getProjectDetails(Request $request)
    {
        $project_id = $request->post('project_id');
        if(!$project_id){
            return $this->error('缺少项目ID');
        }

        if(!in_array($project_id,[1,2,3,4])){
            return $this->error('项目ID不在有效区间');
        }

        $data = ProjectImg::query()->where('project_id',$project_id)->get()->groupBy('type_id')->toArray();
        return $this->success($data);
    }

    /**
     * 编辑主服务价格
     */
    public function mainEdit(Request $request)
    {
        $two = $request->twoHour;
        $threeHour = $request->threeHour;
        $fourHour = $request->fourHour;
        $fiveHour = $request->fiveHour;


        if(!$two || !$threeHour || !$fourHour || !$fiveHour){
            return $this->error('参数缺失');
        }

        if(!is_numeric($two) || !is_numeric($threeHour) || !is_numeric($fourHour) || !is_numeric($fiveHour)){
            return $this->error('价格参数有误');
        }

        if($two < 0 || $threeHour < 0 || $fourHour < 0 || $fiveHour < 0){
            return $this->error('价格不能小于0');
        }

        DB::beginTransaction();
        try{
            DailyCleaning::query()->updateOrCreate(['hour'=>120],['price'=>$two]);
            DailyCleaning::query()->updateOrCreate(['hour'=>180],['price'=>$threeHour]);
            DailyCleaning::query()->updateOrCreate(['hour'=>240],['price'=>$fourHour]);
            DailyCleaning::query()->updateOrCreate(['hour'=>300],['price'=>$fiveHour]);
            DB::commit();
            return $this->success();
        }catch(\Exception $e){
            DB::rollBack();
            return $this->error();
        }

    }

    /*
     * 新居开荒价格编辑
     */
    public function wastelandEdit(Request $request)
    {
        $basics_price = $request->basics_price;
        $increase_price = $request->increase_price;

        if(!$basics_price || !$increase_price){
            return $this->error('缺失参数');
        }

        if(!is_numeric($basics_price) || !is_numeric($increase_price)){
            return $this->error('价格参数有误');
        }

        if($basics_price < 0 || $increase_price < 0){
            return $this->error('价格不能小于0');
        }

        Wasteland::query()->forceDelete();
        Wasteland::query()->create(['basics_price'=>$basics_price,'increase_price'=>$increase_price]);

        return $this->success();

    }

    /**
     * 新增图片
     */
    public function addImg(Request $request)
    {
        if ($request->isMethod('POST')) {
            $project_id = $request->post('project_id');
            $type_id = $request->post('type_id');

            if(!$project_id){
                return $this->error('缺少项目ID');
            }

            if(!in_array($project_id,[1,2,3,4])){
                return $this->error('不存在的项目');
            }

            if(!in_array($type_id,[1,2,3,4])){
                return $this->error('不存在的类型');
            }

            $fileCharater = $request->file('file');
            if(!$fileCharater){
                return $this->error('请上传文件');
            }

            $service = new UploadServices();
            $result = $service->upload($fileCharater);

            if($result['status'] == 200){

                ProjectImg::query()->create(
                    [
                        'project_id'=>$project_id,
                        'type_id'=>$type_id,
                        'img'=>$result['url']
                    ]
                );
                return $this->success();
            }else{
                return $this->error('上传失败');
            }
        }
    }

    /**
     * 添加附加服务
     */
    public function servicesAdd(Request $request)
    {
        $service_name = $request->name;
        $service_price = $request->price;
        if(!$service_name || !$service_price || !is_numeric($service_price)){
            return $this->error('参数缺失或者价格参数有误');
        }

        if(mb_strlen($service_name,'UTF8') > 20){
            return $this->error('服务名称超过最大限制');
        }

        AdditionalServices::query()->create([
            'project_id'=>1,
            'services_name'=>$service_name,
            'services_price'=>$service_price,
            'services_status'=>1
        ]);

        return $this->success();

    }

    /**
     * 附加项目编辑
     */
    public function servicesEdit(Request $request)
    {
        $id = $request->id;
        $price = $request->price;
        if(!$id || !$price || !is_numeric($price)){
            return $this->error('参数缺失');
        }
        $service = AdditionalServices::query()->where('id',$id)->first();
        if(!$service){
            return $this->error('该项目不存在');
        }

        $service->services_price = $price;
        $service->save();
        return $this->success();
    }

    /**
     * 删除图片
     */
    public function delImg(Request $request)
    {
        $project_id = $request->project_id;
        $type_id = $request->type_id;
        $img = $request->img;

        if(!$project_id){
            return $this->error('缺少项目ID');
        }

        if(!in_array($project_id,[1,2,3,4])){
            return $this->error('不存在的项目');
        }

        if(!in_array($type_id,[1,2,3,4])){
            return $this->error('不存在的类型');
        }

        if(!$img){
            return $this->error('图片链接缺失');
        }

        $this->unlink($img);

        $res = ProjectImg::query()->where(['project_id'=>$project_id,'type_id'=>$type_id,'img'=>$img])->first();

        if(!$res){
            return $this->error('文件不存在');
        }
        ProjectImg::query()->where(['project_id'=>$project_id,'type_id'=>$type_id,'img'=>$img])->delete();
        return $this->success();

    }

    /**
     * 日常保洁附加服务上下架
     */
    public function serviceFrames(Request $request)
    {
        if(!isset($request->status) || !isset($request->id)){
            return $this->error();
        }

        if(!in_array($request->status,[1,2])){
            return $this->error();
        }

        $service = AdditionalServices::query()->where('id',$request->id)->first();

        if(!$service){
            return $this->error();
        }

        $service->services_status = $request->status;
        $service->save();

        return $this->success();
    }

}
