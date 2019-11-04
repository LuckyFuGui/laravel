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
        $lever = LeaveLog::query()->first();
        $worker_ids = Workers::query()->pluck('id')->toArray();
        if($lever){
            LeaveLog::query()->chunk(10, function ($items) use ($worker_ids) {
                foreach ($items as $item){
                    if (in_array($items->worker_id,$worker_ids)) {
                        if(time() > strtotime($items->end_at)){
                            continue;
                        }else{
                            Workers::query()->where('id',$item->id)->update(['is_lever'=>1]);
                        }
                    }
                }
            });
        }
        info('实时处理');
    }
}
