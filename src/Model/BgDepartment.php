<?php


namespace Jxm\Ehr\Model;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jxm\Tool\Helper\Tree\TreeModel;

/**
 * Jxm\Ehr\Model\BgDepartment
 *
 * @property int $id
 * @property string $name 部门名
 * @property int $parent_id 上级ID
 * @property int $type 部门类型
 * @property string $group_id 所属集团
 * @property string $describe 描述信息
 * @property int $state 部门状态
 * @property string|null $address 详细地址
 * @property string|null $subway 地铁路线
 * @property string|null $bus_routes 公交乘坐
 * @property int $editor_id 编辑人ID
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $area_id 所属片区ID
 * @property int $charger_id 负责人
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment newQuery()
 * @method static \Illuminate\Database\Query\Builder|BgDepartment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment query()
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereBusRoutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereChargerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereDescribe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereEditorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereSubway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgDepartment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|BgDepartment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BgDepartment withoutTrashed()
 * @mixin \Eloquent
 */
class BgDepartment extends EhrBasicModel
{
    use SoftDeletes;
    use TreeModel;

    const Type_Init = 1;        //初始化部门
    const Type_Function = 3;    //职能部门
    const Type_Normal = 5;      //普通部门
    const Type_Business_Division = 16;   //事业部
    const Type_Branch_Company = 18;     //分公司
    const Type_Sub_Division = 19;       //分部
    const Type_Proxy = 20;      //代理
    const Type_Project = 21;    //项目部

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

    public function charger(): BelongsTo
    {
        return $this->belongsTo(UserInfo::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(UserInfo::class);
    }


    #region Functions
    public function isCompany()
    {
        return $this->isTop();
    }

    /**
     * Notes: 获取所属公司
     * User: harden - 2021/9/28 下午2:15
     * @param null $nodes
     * @return mixed
     */
    public function getCompany($nodes = null)
    {
        if (is_null($nodes)) {
            $allDeparts = BgDepartment::with(['group:id,name'])->get(['id', 'type', 'name', 'parent_id', 'group_id']);
            $nodes = array_column($allDeparts, null, 'id');
        } elseif (!is_array($nodes)) {
            $nodes = array_column($nodes, null, 'id');
        }
        $node = $nodes[$this->id];
        while ($node['parent_id'] &&
            isset($nodes[$node['parent_id']]) && $nodes[$node['parent_id']]) {
            $node = $nodes[$node['parent_id']];
        }
        return $nodes[$node['id']];
    }

    /**
     * Notes: 获取所属分公司级
     * User: harden - 2022/11/4 上午9:44
     */
    public function getBranchCompany($nodes = null)
    {
        if (is_null($nodes)) {
            $allDeparts = BgDepartment::with(['group:id,name'])->get(['id', 'type', 'name', 'parent_id', 'group_id']);
            $nodes = array_column($allDeparts, null, 'id');
        } elseif (!is_array($nodes)) {
            $nodes = array_column($nodes, null, 'id');
        }
        $node = $nodes[$this->id];
        while ($node['parent_id'] && isset($nodes[$node['parent_id']]) &&
            $nodes[$node['parent_id']] &&
            $node['type'] != self::Type_Branch_Company) {
            $node = $nodes[$node->parent_id];
        }
        return $nodes[$node['id']];
    }
    #endregion

}
