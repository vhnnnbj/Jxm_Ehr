<?php


namespace Jxm\Ehr\Model;


use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\UserInfo
 *
 * @property int $id
 * @property string $name 用户名
 * @property int $sex 0,未定，1，男，2，女
 * @property string $identity 身份证
 * @property string $phone 联系电话
 * @property string|null $address 住址
 * @property string|null $email 电子邮箱
 * @property string $salary_password 工资条密码(md5)
 * @property string|null $details 履历信息
 * @property string|null $entry_time 入职时间
 * @property string|null $leave_time 离职时间
 * @property string|null $describe 用户描述
 * @property int|null $editor_id 最后编辑者Id
 * @property int $state 用户状态
 * @property string|null $img 头像链接
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $main_pos_type
 * @property int|null $main_pos_id
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo newQuery()
 * @method static \Illuminate\Database\Query\Builder|UserInfo onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereDescribe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereEditorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereEntryTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereIdentity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereLeaveTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereMainPosId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereMainPosType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereSalaryPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|UserInfo withTrashed()
 * @method static \Illuminate\Database\Query\Builder|UserInfo withoutTrashed()
 * @mixin \Eloquent
 */
class UserInfo extends EhrBasicModel
{
    use SoftDeletes;

    const Sex_Unknown = 0;
    const Sex_Man = 1;
    const Sex_Male = 2;
    
    const State_Normal = 0;         //正常
    const State_Trail = 5;          //试用
    const State_Leave = 10;          //离职
    const State_Black = 20;          //黑名单
    const State_Exception = 30;      //异常
    const State_Unchecked = 40;      //未审核

    public function archive(): HasOne
    {
        return $this->hasOne(ArchiveInfo::class, 'userinfo_id');
    }
}
