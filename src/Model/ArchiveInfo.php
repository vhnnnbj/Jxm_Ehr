<?php


namespace Jxm\Ehr\Model;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchiveInfo extends EhrBasicModel
{
    public function userinfo(): BelongsTo
    {
        return $this->belongsTo(UserInfo::class);
    }

    public function superior(): BelongsTo
    {
        return $this->belongsTo(UserInfo::class);
    }
}
