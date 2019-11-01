<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Workers extends Model
{
    // 表名
    protected $table  = 'workers';
    protected $keepRevisionOf = null;
    // 屏蔽字段
  	protected $guarded=[];

    protected $fillable = [
        'uid', 'img', 'project_ids', 'name', 'sex', 'phone',
        'status', 'entry_at','quit_at'
    ];

    /**
     * 用户模型关联
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'uid');
    }

    /**
     * 员工详情关联
     * @return mixed
     */
    public function worker_details()
    {
        return $this->hasOne('App\Models\WorkerDetails', 'worker_id');
    }
}
