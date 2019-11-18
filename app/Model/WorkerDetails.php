<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


class WorkerDetails extends Model
{
    // 表名
    protected $table  = 'workers_details';
    protected $keepRevisionOf = null;
    // 屏蔽字段
  	protected $guarded=[];

    protected $fillable = [
        'worker_id', 'month_times', 'month_price', 'month_evaluate', 'waiting_at', 'times',
        'price', 'evaluate'
    ];

    /**
     * 用户模型关联
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'worker_id');
    }
}
