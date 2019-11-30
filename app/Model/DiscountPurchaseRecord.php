<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DiscountPurchaseRecord extends Model
{
    // 表名
    protected $table  = 'discount_purchase_record';
    protected $keepRevisionOf = null;
    // 屏蔽字段
  	protected $guarded=[];


    public function discount()
    {
        return $this->belongsTo('App\Model\Discount','discount_id');
    }
}
