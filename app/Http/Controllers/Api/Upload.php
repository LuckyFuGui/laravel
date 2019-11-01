<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\UploadServices;

class Upload extends Controller
{

    public function upload(Request $request)
    {

        if ($request->isMethod('POST')) {

            $fileCharater = $request->file('file');
            if(!$fileCharater){
                return $this->error('请上传文件');
            }

            $service = new UploadServices();
            $result = $service->upload($fileCharater);

            if($result['status'] == 200){
                return $this->success($result['url']);
            }else{
                return $this->error();
            }
        }
    }
}
