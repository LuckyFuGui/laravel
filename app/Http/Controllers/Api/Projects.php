<?php

namespace App\Http\Controllers\Api;

use App\Model\AdditionalServices;
use App\Model\DailyCleaning;
use App\Model\Dictionary;
use App\Model\ProjectImg;
use App\Model\Wasteland;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\UploadServices;
use PharIo\Manifest\RequiresElement;

class Projects extends Controller
{

    /**
     * 获取项目详情
     * @param Request $request
     * @return array
     */
    public function getProjectDetails(Request $request)
    {

        $project_id = $request->project_id;
        if(!$project_id || !in_array($project_id,[1,2,3,4])){
            return $this->error('缺失有效项目ID');
        }

        $data = ProjectImg::query()->where('project_id',$project_id)->get()->groupBy('type_id')->toArray();

        return $this->success($data);
    }

    /**
     * 获取日常保洁/新居开荒项目数据
     */
    public function getProjectData(Request $request)
    {
        if(!isset($request->project_id)){
            return $this->error('请提供项目ID');
        }

        if(!in_array($request->project_id,[1,4])) {
            return $this->error('项目ID有误');
        }

        switch ($request->project_id){
            case 1:
                $daily = DailyCleaning::query()->select(['id','hour','price'])->get()->toArray();
                $service = AdditionalServices::query()->where('services_status',1)->get()->toArray();
                $data['daily'] = $daily;
                $data['service'] = $service;
                return $this->success($data);
                break;
            case 4:
                $wasteland = Wasteland::query()->get()->toArray();
                return $this->success($wasteland);
                break;
            default:
                return $this->error('获取数据错误');
                break;
        }
    }

    /**
     * 获取所有启用状态的枚举数据
     */
    public function getDictionaryData()
    {
        $data = Dictionary::query()->where('status',1)->get()->toArray();
        return $data;
    }
}
