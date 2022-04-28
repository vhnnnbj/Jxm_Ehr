<?php

namespace Jxm\Ehr\Helper\Relations;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Jxm\Ehr\Model\Role;
use Jxm\Tool\Helper\Tree\TreeModel;
use Nwidart\Modules\Collection;

trait HasTreeRelations
{
    use TreeModel;

    abstract function relations(): HasMany;

    public static function mineCategories()
    {
        return null;
    }

    public static function getRelationQuery(): Builder
    {
        throw new \Exception('need set relation query!', 500);
    }

//    /**
//     * Notes:获取授权关系
//     * User: harden - 2022/1/24 上午11:34
//     * @param $error
//     * @param null $operate
//     * @param null $category_id
//     * @param null $bg_id
//     * @param null $area_id
//     * @param null $department_id
//     * @param null $role_id
//     * @return Collection|LessonCategory[]|null
//     */
//    public static function getAllRelations(&$error, $operate = null, $category_id = null, $bg_id = null, $area_id = null,
//                                           $department_id = null, $role_id = null)
//    {
//        $relations = static::getRelationQuery()->with(['bg:id,name',
//            'area:id,name', 'department:id,name', 'role:id,name',
//            'category:id,name,parent_id']);
//        if ($operate) {
//            $relations->whereRaw('operate & ' . $operate . '!=0');
//        }
//        if ($category_id) $relations->where('category_id', $category_id);
//        if ($bg_id) $relations->where('bg_id', $bg_id);
//        if ($area_id) $relations->where('area_id', $area_id);
//        if ($department_id) $relations->where('department_id', $department_id);
//        if ($role_id) $relations->where('role_id', $role_id);
//        return $relations->get();
//    }

    public static function getOwnCategories(&$error, $roleInfos, $operate, $allCats, $category_id = null)
    {
        /**
         * 先获取个人所有角色及所在部门
         */
//        $roleInfos = Auth::user()->getEhrRoles($error);
//        if ($error) return null;
//        $allCats = self::getSimple();
        $categories = $allCats;
        $allRelations = self::getRelationQuery()->with(['category']);
        if ($category_id) {
            $category = self::whereId($category_id)->first();
            $categories->whereIn('id', $category->getAllChildrenIds($allCats));
            $allRelations->whereIn('category_id', array_merge($category->getAllChildrenIds($allCats),
                $category->getUpGrades($allCats)));
        }

        $allRelations = $allRelations->get();
        Log::info('all_relation', ['all' => $allRelations]);
        $has_categories = [];
        $own_categories = self::mineCategories();
        if ($own_categories)
            foreach ($own_categories as $category) {
                $has_categories[] = [
                    'category_id' => $category->id,
                    'role_id' => '',
                    'operate' => 11,
                    'relation' => [],
                ];
            }
        foreach ($allRelations as $relation) {
            $matchRoles = Arr::where($roleInfos, function ($item) use ($relation) {
                $pass = true;
                if ($relation->role_id)
                    $pass = ($item['id'] == $relation->role_id);
                if ($pass && $relation->department_id) {
                    $pass = in_array($relation->department_id,
                        $item['position']['upgrade_department_ids']);
                }
                if ($pass && $relation->area_id) {
                    $pass = in_array($relation->area_id,
                        $item['position']['upgrade_area_ids']);
                }
                if ($pass && $relation->bg_id) {
                    $pass = ($relation->bg_id == $item['position']['bg_id']);
                }
                return $pass;
            });
            Log::info('match', ['all' => $matchRoles]);
            foreach ($matchRoles as $role) {
                foreach ($relation->category->getAllChildrenIds($allCats) as $item) {
                    $sub_has = [
                        'category_id' => $item,
                        'role_id' => $relation->role_id,
                        'operate' => $relation->operate,
                        'relation' => $relation,
                    ];
                    switch ($role['type']) {
                        case Role::Type_Platform_All:
                        case Role::Type_Platform_Normal:
                            $sub_has['scope_type'] = 'all';
                            $sub_has['scopes'] = '';
                            break;
                        case Role::Type_BG_Admin:
                        case Role::Type_BG_Normal:
                            $sub_has['scope_type'] = 'bg';
                            $sub_has['scopes'] = $role['bg_id'];
                            break;
                        case Role::Type_Area_Admin:
                        case Role::Type_Area_Normal:
                            $sub_has['scope_type'] = 'area';
                            $sub_has['scopes'] = array_unique(array_merge($role['position']['upgrade_area_ids'],
                                $role['position']['sub_area_ids']));
                            break;
                        case Role::Type_Area_Sub:
                            $sub_has['scope_type'] = 'area';
                            $sub_has['scopes'] = $role['position']['sub_area_ids'];
                            break;
                        case Role::Type_Company_Admin:
                        case Role::Type_Company_Normal:
                            $sub_has['scope_type'] = 'department';
                            $sub_has['scopes'] = array_unique(array_merge($role['position']['upgrade_department_ids'],
                                $role['position']['sub_department_ids']));
                            break;
                        case Role::Type_Department_Normal:
                            $sub_has['scope_type'] = 'department';
                            $sub_has['scopes'] = $role['position']['sub_department_ids'];
                            break;
                        default:
                            break;
                    }
                    $has_categories[] = $sub_has;
                }
            }
        }
        $allOperates = [];
        Log::info('$allOperates', ['all' => $allOperates]);
        foreach (array_unique(Arr::pluck($has_categories, 'category_id')) as $subCategory_id) {
            if ($category_id) {
                if (!in_array($subCategory_id, $category->getAllChildrenIds($allCats)))
                    continue;
            }
            $item = ['category_id' => $subCategory_id];
            $operates = Arr::where($has_categories, function ($q) use ($subCategory_id, $operate) {
                return $q['category_id'] == $subCategory_id && ($q['operate'] & $operate);
            });
            if (sizeof($operates) == 0) continue;
            $sum = 0;
            $roles = [];
            $relation = [];
            $scopes = [];
            foreach ($operates as $subOperate) {
                $sum = $sum | $subOperate['operate'];
                $roles[] = $subOperate['role_id'];
                if (($subOperate['operate'] & RelationModel::Operate_ScopeManage)
                    && !($subOperate['operate'] & RelationModel::Operate_AllManage)) {
                    if (sizeof(Arr::where($scopes, function ($item) use ($subOperate) {
                            return $item['scope_type'] == $subOperate['scope_type'] &&
                                $item['scopes'] == $subOperate['scopes'];
                        })) == 0) {
                        $scopes[] = [
                            'operate' => $subOperate['operate'],
                            'scope_type' => $subOperate['scope_type'],
                            'scopes' => $subOperate['scopes'],
                        ];
                    }
                }
//                $roles[] = join(',', $operate['relation']);
            }
            if ($sum & RelationModel::Operate_AllManage) {
                $scopes = [];
            }
            $item['scopes'] = $scopes;
            $item['operate'] = $sum;
            $item['roles'] = join(',', $roles);
            $allOperates[] = $item;
        }
        return $allOperates;
    }

}
