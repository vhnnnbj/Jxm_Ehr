<?php


namespace Jxm\Ehr\Model;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jxm\Tool\Helper\Tree\TreeModel;

class BgArea extends EhrBasicModel
{
    use TreeModel;

    const State_Normal = 0;
    const State_Stop = 50;

    public function bg(): BelongsTo
    {
        return $this->belongsTo(BgGroup::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(BgDepartment::class, 'area_id');
    }
}
