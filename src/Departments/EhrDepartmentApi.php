<?php


namespace Jxm\Ehr\Departments;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Jxm\Ehr\JxmEhrAccessHelper;

class EhrDepartmentApi
{
    #region Caches
    const DepartmentInfos_Prifix = 'Jxm:Ehr:BG:Department:';
//    const Infix_List = 'List';
//    const Infix_Tree = 'Trees';
//    const Infix_Updated = 'Updated_at';
    const Infix_One = 'One';

//    private static function setTree($trees)
//    {
//        Redis::setex(self::DepartmentInfos_Prifix . self::Infix_Tree, 3600, json_encode($trees));
//        self::toList($departments, $trees);
//        Redis::setex(self::DepartmentInfos_Prifix . self::Infix_List, 3600, json_encode($departments));
//        Redis::setex(self::DepartmentInfos_Prifix . self::Infix_Updated, 3600,
//            Arr::last(Arr::sort($departments, 'updated_at'))['updated_at']);
//    }
//
//    private static function toList(&$list, $trees, $fields = ['id', 'name', 'parent_id', 'updated_at'])
//    {
//        foreach ($trees as $tree) {
//            $list[] = Arr::only($tree, $fields);
//            self::toList($list, $tree['children'], $fields);
//        }
//    }
//
//    private static function getTree()
//    {
//        $tree = Redis::get(self::DepartmentInfos_Prifix . self::Infix_Tree);
//        if ($tree) {
//            $tree = json_decode($tree, true);
//        }
//        return $tree;
//    }

    public static function cacheAll($departments)
    {
        foreach ($departments as $department) {
            Redis::setex(self::DepartmentInfos_Prifix . self::Infix_One . ':' . $department['id']
                , 3600, json_encode($department));
        }
    }

    public static function getOne($id)
    {
        $info = Redis::get(self::DepartmentInfos_Prifix . self::Infix_One . ':' . $id);
        return $info ? json_decode($info, true) : null;
    }

//    private static function checkUpdate(): bool
//    {
//        if (!config('ehr.bg_id', null)) {
//            return false;
//        }
//        if (!Auth::user() || !Auth::user()->ehr_token) {
//            return false;
//        }
//        $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'bg/department/checkUpdate',
//            Auth::user()->ehr_token, [
//                'group_id' => config('ehr.bg_id'),
//            ]);
//        $updated_at = $error ? false : $result['updated_at'];
//        $old = Redis::get(self::DepartmentInfos_Prifix . self::Infix_Updated);
//        if (!$old || !Carbon::parse($old)->eq($updated_at)) {
//            return false;
//        }
//        return true;
//    }
//
    public static function updateDepartments(array $department_ids)
    {
        $departments = [];
        $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'bg/department/someInfos',
            Auth::user()->ehr_token, [
                'department_ids' => join(',', $department_ids),
            ]);
        if ($error) throw new \Exception($error);
        $departments = $result['data']['departments'];
        self::cacheAll($departmentsr);
        return $departments;
    }

    public static function getDepartments(array $department_ids, $fields = null)
    {
        $departments = [];
        $none = [];
        foreach ($department_ids as $department_id) {
            $department = EhrDepartmentApi::getOne($department_id);
            if ($department)
                $departments[] = $department;
            else
                $none[] = $department_id;
        }
        if (sizeof($none) > 0) {
            $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'bg/department/someInfos',
                Auth::user()->ehr_token, [
                    'department_ids' => join(',', $none),
                    'fields' => $fields,
                ]);
            if ($error) throw new \Exception($error);
            $get_departments = $result['data']['departments'];
            self::cacheAll($get_departments);
            $departments = array_merge($departments, $get_departments);
        }
        return $departments;
    }

    #endregion

//    public static function getOne($department_id)
//    {
//        self::updateDepartments();
//        $dpeartment = Redis::get(self::DepartmentInfos_Prifix . self::Infix_One . ':' . $department_id);
//        if ($department) {
//            return json_decode($department, true);
//        }
//        return $department;
//    }

    /**
     * Notes: 获取部门树结构
     * User: harden - 2021/11/8 下午5:03
     * @param $error
     * @param null $department_id 指定部门
     * @return mixed|null
     */
//    public static function getTrees(&$error, $bg_id = null, $department_id = null)
//    {
//        if (!Auth::user() || !Auth::user()->ehr_token) {
//            $error = '请先登录用户！';
//            return null;
//        }
//        $trees = self::updateDepartments();
//        if (!$department_id) {
//            return $trees;
//        } else {
//            return self::searchNode($trees, $department_id);
//        }
//    }
//
//    private static function searchNode($trees, $department_id)
//    {
//        foreach ($trees as $tree) {
//            if ($tree['id'] == $department_id) {
//                return $tree;
//            }
//            if (sizeof($tree['children']) > 0) {
//                $result = self::searchNode($tree['children'], $department_id);
//                if ($result) return $result;
//            }
//        }
//        return null;
//    }
}
