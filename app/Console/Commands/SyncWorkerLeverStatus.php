<?php

namespace App\Console\Commands;


use App\Model\LeaveLog;
use App\Model\Wasteland;
use App\Model\Workers;
use App\Models\PlSpu;
use App\Model\DailyCleaning;
use App\Services\Api\PlSpuApiService;
use Illuminate\Console\Command;

class SyncWorkerLeverStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync_worker_lever_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '刷新员工请假状态';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //更新请假状态
        $worker_ids = Workers::query()->pluck('id')->toArray();
        LeaveLog::query()->chunk(100, function ($items) use ($worker_ids) {
            foreach ($items as $item){
                if (in_array($item->worker_id,$worker_ids) && !in_array($item->status,[2,3])) {
                    if(time() > strtotime($item->end_at)){
                        //请假已结束
                        $item->status = 4;
                    }elseif (time() < strtotime($item->begin_at)){
                        //请假还没开始
                        $item->status = 0;
                    }elseif (time() > strtotime($item->begin_at) && time() < strtotime($item->end_at)){
                        //正在进行中
                        $item->status = 1;
                    }

                    $item->save();
                }
            }
        });

        //修改员工的请假状态
        //如果员工有请假中的状态，则员工状态为请假中
        //如果没有请假中的状态，则员工状态为正常
        Workers::query()->chunk(100,function ($worker){
            foreach ($worker as $value){
                $status = LeaveLog::query()->where('worker_id',$value->id)->pluck('status')->toArray();
                if(in_array(1,$status)){
                    $value->is_leave = 1;
                }else{
                    $value->is_leave = 0;
                }
                $value->save();
            }
        });


        info('实时处理');
    }
}
