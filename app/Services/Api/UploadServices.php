<?php

namespace App\Services\Api;


use App\Models\Site;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UploadServices
{
    public function upload($fileCharater)
    {

        if ($fileCharater->isValid()) {

            $ext = $fileCharater->getClientOriginalExtension();

            if (!in_array($ext, ['jpg', 'png', 'gif', 'jpeg'])) {
                return ['status'=>201];
            }

            //获取文件的绝对路径
            $path = $fileCharater->getRealPath();

            $filename = time() . rand(10000, 99999) . '.' . $ext;

            Storage::disk('uploads')->put($filename, file_get_contents($path));

            $url = 'uploads/' . $filename;

            return ['status'=>200,'url'=>$url];
        }

        return ['status'=>202];

    }

}
