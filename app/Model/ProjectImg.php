<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProjectImg extends Model
{
    // 表名
    protected $table  = 'project_img';
    protected $keepRevisionOf = null;
    // 屏蔽字段
  	protected $guarded=[];

    protected $fillable = [
        'project_id', 'type_id', 'img'
    ];
}
