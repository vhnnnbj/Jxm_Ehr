<?php

namespace Jxm\Ehr\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jxm\Tool\CommonClass\RecordTemplate;

/**
 * Jxm\Ehr\Model\EsbMessageRecord
 *
 * @property int $id
 * @property string|null $model_type
 * @property string|null $model_id
 * @property int $type 记录类型
 * @property string $subtype 记录子类型
 * @property int|null $state 可用标志状态
 * @property string|null $param 记录参数
 * @property string|null $describe 记录简述
 * @property int|null $editor_id 编辑人ID
 * @property string|null $val_num 备用数值
 * @property string|null $val_str 备用字符串
 * @property string|null $val_time 备用时间
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read UserInfo|null $editor
 * @property-read Model|\Eloquent $model
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereDescribe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereEditorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereParam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereSubtype($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereValNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereValStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EsbMessageRecord whereValTime($value)
 * @mixin \Eloquent
 */
class EsbMessageRecord extends Model
{
    use RecordTemplate;

    protected $fillable = RecordFillable;

    public function editor(): BelongsTo
    {
        return $this->belongsTo(UserInfo::class)
            ->withDefault(['username' => 'ESB', 'id' => 0]);
    }

    /**
     * Notes: 写日志
     * User: harden - 2021/9/14 下午2:48
     * @param $type
     * @param $subtype
     * @param $state
     * @param $param
     * @param $describe
     * @param null $editor_id
     * @param null $val_num
     * @param null $val_str
     * @param null $val_time
     * @return static
     */
    public static function makeRecord($type, $subtype, $state, $param, $describe,
                                      $editor_id = null, $val_num = null, $val_str = null, $val_time = null)
    {
        $record = new static([
            'type' => $type,
            'subtype' => $subtype,
            'state' => $state,
            'describe' => $describe,
            'editor_id' => $editor_id ?: 0,
            'param' => is_array($param) ? json_encode($param) : $param,
            'val_num' => $val_num,
            'val_str' => $val_str,
            'val_time' => $val_time,
        ]);
        $record->save();
        return $record;
    }
}
