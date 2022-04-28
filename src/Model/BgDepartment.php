<?php


namespace Jxm\Ehr\Model;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jxm\Tool\Helper\Tree\TreeModel;

class BgDepartment extends EhrBasicModel
{
    use TreeModel;

    const Type_Init = 1;    //初始化部门
    const Type_Function = 3;    //职能部门
    const Type_Normal = 5;  //普通部门

    const State_Normal = 0; //正常
    const State_Stop = 50;  //停运

    public function group(): BelongsTo
    {
        return $this->belongsTo(BgGroup::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(BgArea::class);
    }
}
