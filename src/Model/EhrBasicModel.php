<?php


namespace Jxm\Ehr\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Jxm\Ehr\JxmEhrAccessHelper;

class EhrBasicModel extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $conn = JxmEhrAccessHelper::getConn();
        config()->set('database.connections.mysql_ehr', $conn);
        $this->connection = 'mysql_ehr';
    }
}
