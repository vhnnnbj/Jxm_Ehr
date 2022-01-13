<?php


namespace Jxm\Ehr\Model;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BgDepartment extends EhrBasicModel
{
    public function group(): BelongsTo
    {
        return $this->belongsTo(BgGroup::class);
    }
}
