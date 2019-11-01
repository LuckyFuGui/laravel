<?php

namespace App\Http\Controllers\Web;

use App\Model\Workers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\UploadServices;
use PhpMyAdmin\MoTranslator\ReaderException;

class Worker extends Controller
{
	/**
	 * 员工列表
	 */
    public function index(Request $request)
    {
        $request->page < 1 ? 1 : $request->page;
        $request->limit > 20 ? 20 : $request->page;
	    $query = Workers::query();
        $data = $query->with('worker_details')
            ->offset(($request->page - 1) * $request->limit)
            ->limit($request->limit)
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

        $worker = Workers::query()->with('worker_details')->where('id',$request->id)->first();
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
        $uid = $request->uid ?? '';
        $img = $request->img;
        $name = $request->name;
        $phone = $request->phone;
        $sex = $request->sex;
        $project_ids = $request->project_ids;
        $entry_at = $request->entry_at;
        if(!$uid){
            return $this->error('缺少用户ID');
        }

        if(!$img){
            return $this->error('请上传头像');
        }

        if(!$name){
            return $this->error('请上传头像');
        }

        if(!('/^[\u4e00-\u9fa5]+$/'.test($name))) {
            return $this->error('请输入中文名字');
        }

        if(!is_numeric($phone) || strlen($phone) < 9){
            return $this->error('请输入纯数字电话号码');
        }

        if(!$sex){
            return $this->error('请输入性别');
        }

        if(!$project_ids){
            return $this->error('请选择服务项目');
        }

        if(!$entry_at){
            return $this->error('请选择入职时间');
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
        if(!$phone){
            return $this->error();
        }


        $user = User::query()->where('phone',$phone)->first();
        if(!$user){
            return $this->error();
        }

        return $this->success($user->id);

    }

    /**
     * 员工信息修改
     */
    public function update(Request $request)
    {
        $id = $request->id ?? '';
        $uid = $request->uid ?? '';
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
            return $this->error();
        }


        if(!$uid){
            return $this->error('缺少用户ID');
        }

        if(!$img){
            return $this->error('请上传头像');
        }

        if(!$name){
            return $this->error('请上传头像');
        }

        if(!('/^[\u4e00-\u9fa5]+$/'.test($name))) {
            return $this->error('请输入中文名字');
        }

        if(!is_numeric($phone) || strlen($phone) < 9){
            return $this->error('请输入纯数字电话号码');
        }

        if(!$sex){
            return $this->error('请输入性别');
        }

        if(!$project_ids){
            return $this->error('请选择服务项目');
        }

        if(!$entry_at){
            return $this->error('请选择入职时间');
        }


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


}
