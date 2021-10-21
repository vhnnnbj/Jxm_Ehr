<?php

namespace Jxm\Ehr\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Jxm\Ehr\Model\JxmEhrTokenInfos
 *
 * @property int $id
 * @property string $user_type
 * @property int $user_id
 * @property string $access_token
 * @property string $refresh_token
 * @property string|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|JxmEhrTokenInfos newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JxmEhrTokenInfos newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JxmEhrTokenInfos query()
 * @method static \Illuminate\Database\Eloquent\Builder|JxmEhrTokenInfos whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JxmEhrTokenInfos whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JxmEhrTokenInfos whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JxmEhrTokenInfos whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JxmEhrTokenInfos whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JxmEhrTokenInfos whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JxmEhrTokenInfos whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JxmEhrTokenInfos whereUserType($value)
 * @mixin \Eloquent
 */
class JxmEhrTokenInfos extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'token_type',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public function is_expire()
    {
        return now()->lt(Carbon::parse($this->expires_at));
    }
}
