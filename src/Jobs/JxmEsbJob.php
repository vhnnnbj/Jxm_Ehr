<?php

namespace Jxm\Ehr\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Jxm\Ehr\JxmEsb;
use Jxm\Ehr\Model\EsbMessageRecord;

class JxmEsbJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $route = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($route = null)
    {
        $this->route = $route;
    }

    const Key_Prefix = 'JxmEsbJob:Esb:LastGets';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $info = Redis::get(self::Key_Prefix);
        if (!$info) {
            $after = '2022-07-20 00:00:00';
            $except_ids = '';
        } else {
            $info = json_decode($info, true);
            if ($info) {
                $after = $info['after'] ?? '2022-07-20 00:00:00';
                $except_ids = $info['except_ids'] ?? '';
            } else {
                $after = '2022-07-20 00:00:00';
                $except_ids = '';
            }
        }
        $excepts = explode(',', $except_ids);
        do {
            $gets = JxmEsb::get($after, join(',', $excepts));
            /**
             * Deal Messages
             */
            foreach ($gets as $msg) {
                array_push($excepts, $msg['id']);
                while (sizeof($excepts) > 5) {
                    unset($excepts[0]);
                }
                $after = Carbon::parse($msg['created_at'])->gt($after) ? $msg['created_at'] : $after;
                $record = EsbMessageRecord::makeRecord(1, '获取ESB消息', 0, $msg, '', $msg['editor_id'] ?? null,
                    null, $msg['value'], now());
                $this->deal($msg, $record);
            }
        } while (sizeof($gets) > 0);
        Redis::setex(self::Key_Prefix, 30 * 60, json_encode([
            'after' => $after,
            'except_ids' => join(',', $excepts),
        ]));
    }

    abstract function deal(array $msg, EsbMessageRecord $record): bool;
}
