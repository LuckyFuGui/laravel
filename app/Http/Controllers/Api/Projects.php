<?php

namespace App\Http\Controllers\Api;

use App\Model\ProjectImg;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\UploadServices;

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
}
