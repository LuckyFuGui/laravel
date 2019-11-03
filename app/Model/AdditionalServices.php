<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AdditionalServices extends Model
{
    // 表名
    protected $table  = 'additional_services';
    protected $keepRevisionOf = null;
    // 屏蔽字段
  	protected $guarded=[];

    protected $fillable = [
        'project_id', 'services_name','services_price','services_status',''
    ];
}
