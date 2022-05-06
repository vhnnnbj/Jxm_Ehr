<?php


namespace Jxm\Ehr\Helper\Relations;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Jxm\Ehr\Model\BgArea;
use Jxm\Ehr\Model\BgDepartment;
use Jxm\Ehr\Model\BgGroup;
use Jxm\Ehr\Model\Role;

class RelationModel extends Model
{
    #region Keys
    public $key_model = 'category_id';

    public $key_bg = 'bg_id';
    public $key_area = 'area_id';
    public $key_department = 'department_id';
    public $key_role = 'role_id';

    public $key_operate = 'operate';
    public $key_editor = 'editor_id';
    #endregion

    const Operate_Get = 0b000001;
    const Operate_Modify = 0b000010;
    const Operate_ScopeManage = 0b000100;
    const Operate_AllManage = 0b001000;

    public function bg(): BelongsTo
    {
        return $this->belongsTo(BgGroup::class, $this->key_bg);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(BgArea::class, $this->key_area);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(BgDepartment::class, $this->key_department);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, $this->key_role);
    }

    #region static Functions
    public static function set($model_id, $operate, $bg_id, $area_id = null, $department_id = null,
                               $role_id = null, $editor_id = null)
    {
        $tmp = new self();
        if (!$role_id && $operate != 0) {
            $operate = 0b000001;
        }
        if ($operate & self::Operate_AllManage) {
            $operate = 0b001111;
        } elseif ($operate & self::Operate_ScopeManage) {
            $operate = 0b000111;
        } elseif ($operate & self::Operate_Modify) {
            $operate = 0b000011;
        }

        if ($operate == 0) {
            self::where([
                $tmp->key_model => $model_id,
                $tmp->key_bg => $bg_id ?: '0',
                $tmp->key_area => $area_id,
                $tmp->key_department => $department_id,
                $tmp->key_role => $role_id,
            ])->delete();
            return true;
        } else {
            $relation = self::firstOrNew([
                $tmp->key_model => $model_id,
                $tmp->key_bg => $bg_id ?: '0',
                $tmp->key_area => $area_id,
                $tmp->key_department => $department_id,
                $tmp->key_role => $role_id,
            ]);
            $relation[$tmp->key_operate] = $operate;
            $relation[$tmp->key_editor] = $editor_id ?: Auth::user()->ehr_id;
            $relation->save();
            return $relation;
        }
    }
    #endregion
}
