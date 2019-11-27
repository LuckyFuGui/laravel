<?php

namespace App\Http\Controllers\Web;

use App\Model\LeaveLog;
use App\Model\Order;
use App\Model\Workers;
use App\Model\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\UploadServices;
use PhpMyAdmin\MoTranslator\ReaderException;
use Illuminate\Support\Facades\DB;

class Worker extends Controller
{
	/**
	 * 员工列表
	 */
    public function index(Request $request)
    {
        if(!isset($request->page)){
            $request->page = 1;
        }else{
            isset($request->page) && $request->page < 1 ? 1 : $request->page;
        }


        if(!isset($request->limit)){
            $request->limit = 20;
        }else{
            isset($request->limit) && $request->limit > 20 ? 20 : $request->limit;
        }
	    $query = Workers::query();
        $data = $query->with('worker_details')
            ->offset(($request->page - 1) * $request->limit)
            ->limit($request->limit)
            ->orderBy('id','desc')
            ->get();

        $count = $query->count();
        return $this->successPage($data, $count);
    }

    /*
     * 查看员工信息
     */
    public function cat(Request $request)
    {
        if(!isset($request->worker_id)){
            return $this->error();
        }

        $worker = Workers::query()->with('worker_details')->where('id',$request->worker_id)->first();
        if(!$worker){
            return $this->error('信息不存在');
        }

        return $this->success($worker);
    }

    /**
     * 创建员工
     */
    public function create(Request $request)
    {
        $uid = $request->uid;
        $img = $request->img;
        $name = $request->name;
        $phone = $request->phone;
        $sex = $request->sex;
        $project_ids = $request->project_ids;
        $entry_at = $request->entry_at;

        $this->verification($request);


        $user_id = Workers::query()->where('uid',$uid)->first();
        if($user_id){
            return $this->error('该用户已被绑定');
        }


        $user = User::query()->where('id',$uid)->first();

        if(!$user){
            return $this->error('要绑定的用户不存在');
        }


        Workers::query()->create([
            'uid'=>$uid,
            'img'=>$img,
            'name'=>$name,
            'phone'=>$phone,
            'sex'=>$sex,
            'project_ids'=>$project_ids,
            'entry_at'=>$entry_at
        ]);

        return $this->success();
    }

    /**
     * 通过手机号查询用户
     */
    public function getUserBYTel(Request $request)
    {
        $phone = $request->phone;
        if(!$phone || !is_numeric($phone)){
            return $this->error('请输入合法手机号');
        }

        $user = User::query()->where('phone',$phone)->first();
        if(!$user){
            return $this->error('没有匹配到用户');
        }

        return $this->success($user->id);

    }

    /**
     * 员工信息修改
     */
    public function update(Request $request)
    {
        $id = $request->id;
        $uid = $request->uid;
        $img = $request->img;
        $name = $request->name;
        $phone = $request->phone;
        $sex = $request->sex;
        $project_ids = $request->project_ids;
        $entry_at = $request->entry_at;
        if(!$id){
            return $this->error('缺少主键ID');
        }

        $worker = Workers::query()->where('id',$id)->first();
        if(!$worker){
            return $this->error('员工信息不存在');
        }


        $wid = Workers::query()->where('uid',$uid)->first();
        if($wid && $worker->uid != $uid){
            return $this->error('该用户已被绑定');
        }


        $user = User::query()->where('id',$uid)->first();
        if(!$user){
            return $this->error('用户不存在');
        }



        $this->verification($request);

        $worker->uid = $uid;
        $worker->img = $img;
        $worker->name = $name;
        $worker->phone = $phone;
        $worker->sex = $sex;
        $worker->project_ids = $project_ids;
        $worker->entry_at = $entry_at;
        $worker->save();
        return $this->success();
    }

    /*
     * 校验员工基本信息
     */
    public function verification($request)
    {
        if(!$request->uid){
            return $this->error('缺少用户ID');
        }

        if(!$request->img){
            return $this->error('请上传头像');
        }

        /*if(!preg_match("/^[\u4e00-\u9fa5]+$/",$request->name)) {
            return $this->error('请输入中文姓名');
        }*/

        if(!is_numeric($request->phone) || strlen($request->phone) != 11){
            return $this->error('请输入11位纯数字电话号码');
        }

        if(!$request->sex || !in_array($request->sex,[1,2])){
            return $this->error('请输入性别');
        }

        $project_ids = implode(',',explode(',',$request->project_ids)) ?? '';
        if(!$project_ids || empty($project_ids)){
            return $this->error('请选择合法服务项目');
        }


        if(!$request->entry_at){
            return $this->error('请选择入职时间');
        }

    }

    /**
     * 在职/离职状态变更
     */
    public function updateStatus(Request $request)
    {
        if(!isset($request->id) || !isset($request->status)){
            return $this->error();
        }
        $status = $request->status;

        if(!in_array($status,[1,2])){
            return $this->error('状态不存在');
        }

        $worker = Workers::query()->where('id',$request->id)->first();
        if(!$worker){
            return $this->error('员工信息不存在');
        }

        $request->status == 1 ? $worker->status = 2 : $worker->status = 1;
        $worker->save();

        return $this->success();
    }

    /**
     * 员工请假
     */
    public function leave(Request $request)
    {
        if(!isset($request->id)){
            return $this->error('ID参数缺失');
        }

        if(!isset($request->begin) || !isset($request->end)){
            return $this->error('时间字段缺失');
        }

        if(date('Y-m-d H:i:s',strtotime($request->begin)) != $request->begin ||
            date('Y-m-d H:i:s',strtotime($request->end)) != $request->end){
            return $this->error('时间格式错误');
        }

        if(strtotime($request->begin) < time()){
            return $this->error('开始时间不能小于当前时间');
        }

        if(strtotime($request->begin) > strtotime($request->end)){
            return $this->error('开始时间不能大于结束时间');
        }

        $worker = Workers::query()->where('id',$request->id)->first();
        if(!$worker){
            return $this->error('当前员工不存在');
        }


        //获取当前员工最近的一次请假结束时间判断当前请假是否有时间重复
        $lastLeave = LeaveLog::query()->where('worker_id',$request->id)->orderBy('end_at','desc')->value('end_at');
        if($lastLeave){
            if(strtotime($lastLeave) < $request->begin){
                return $this->error('请假时间有重复，请重新选择时间段');
            }
        }


        //获取当前员工的排班情况
        $scheduling = Order::query()->where('sid',$request->id)->whereBetween('updated_at',[$request->begin,$request->end])->where('pay_type',1)->first();

        if($scheduling){
            return $this->error('该员工有待服务订单，不可请假');
        }

        DB::beginTransaction();
        try{
            //写入请假记录
            LeaveLog::query()->create([
                'worker_id'=>$request->id,
                'begin_at'=>$request->begin,
                'end_at'=>$request->end,
            ]);

            //更新当前请假状态
            if(time() > strtotime($request->begin)){
                Workers::query()->where('id',$request->id)->update(['is_leave'=>0]);
            }
            return $this->success();
        }catch(\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 考勤管理
     */
    public function leaveManagement(Request $request)
    {
        if(!isset($request->page)){
            $request->page = 1;
        }else{
            isset($request->page) && $request->page < 1 ? 1 : $request->page;
        }


        if(!isset($request->limit)){
            $request->limit = 1;
        }else{
            isset($request->limit) && $request->limit > 20 ? 20 : $request->limit;
        }

        $query = LeaveLog::query()->with('worker');


        if(!empty($request->begin)){
            $query->where('begin_at','>=',$request->begin);
        }

        if(!empty($request->end)){
            $query->where('end_at','<=',$request->end);
        }


        $data = $query
            ->offset(($request->page - 1) * $request->limit)
            ->limit($request->limit)
            ->orderBy('id','desc')
            ->get();

        $count = $query->count();
        return $this->successPage($data, $count);
    }

    /**
     * 请假取消
     */
    public function leaveCancel(Request $request)
    {
        $id = $request->id;
        if(!isset($id)){
            return $this->error('缺少ID参数');
        }

        $leave = LeaveLog::query()->where('id',$id)->first();
        if(!$leave){
            return $this->error('当前请假数据不存在');
        }

        if(!in_array($leave->status,[0,1])){
            return $this->error('当前状态不可取消');
        }


        DB::beginTransaction();
        try{
            if($leave->status == 0){
                $leave->status = 3;
            }else{
                $leave->status = 2;
            }
            $leave->save();

            $status = LeaveLog::query()->where('worker_id',$leave->worker_id)->pluck('status')->toArray();
            if(in_array(1,$status)){
                Workers::query()->where('id',$leave->worker_id)->update(['is_leave'=>1]);
            }else{
                Workers::query()->where('id',$leave->worker_id)->update(['is_leave'=>0]);
            }
            DB::commit();
            return $this->success();
        }catch(\Exception $e){
            DB::rollBack();
            return $this->error();
        }
    }
}
