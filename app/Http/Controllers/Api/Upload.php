<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class Upload extends Controller
{

    public function upload(Request $request)
    {

        if ($request->isMethod('POST')) {

            $fileCharater = $request->file('file');

            if ($fileCharater->isValid()) {

                $ext = $fileCharater->getClientOriginalExtension();

                //获取文件的绝对路径
                $path = $fileCharater->getRealPath();

                $filename = time().rand(10000,99999). '.' .$ext;

                Storage::disk('uploads')->put($filename, file_get_contents($path));

                $url = 'uploads/'. $filename;

                return $this->success();
            }
        }
        return $this->error(['url'=>'',201,'请求方式有误']);
    }
}
