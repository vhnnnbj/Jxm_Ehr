<?php


namespace Jxm\Ehr\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Jxm\Ehr\JxmEhrAccessHelper;

class EhrBasicModel extends Model
{
    use SoftDeletes;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $conn = JxmEhrAccessHelper::getConn();
        config()->set('database.connections.mysql_ehr', $conn);
        $this->connection = 'mysql_ehr';
    }

    /**
     * Notes: 为数组 / JSON 序列化准备日期。
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
