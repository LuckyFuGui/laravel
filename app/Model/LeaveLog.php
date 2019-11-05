<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LeaveLog extends Model
{
    // 表名
    protected $table  = 'leave_log';
    protected $keepRevisionOf = null;
    // 屏蔽字段
  	protected $guarded=[];

    protected $fillable = [
        'worker_id', 'begin_at','end_at'
    ];
}
