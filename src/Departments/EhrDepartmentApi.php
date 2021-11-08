<?php


namespace Jxm\Ehr\Departments;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Jxm\Ehr\JxmEhrAccessHelper;

class EhrDepartmentApi
{
    #region Caches
    const DepartmentInfos_Prifix = 'Jxm:Ehr:BG:Department';

    private static function setTree($trees)
    {
        Redis::setex(self::DepartmentInfos_Prifix . ':Trees', 3600, json_encode($trees));
        self::toList($departments, $trees);
        Redis::setex(self::DepartmentInfos_Prifix . ':List', 3600, json_encode($departments));
        Redis::setex(self::DepartmentInfos_Prifix . ':Updated_at', 3600,
            Arr::last(Arr::sort($departments, 'updated_at'))['updated_at']);
    }

    private static function toList(&$list, $trees, $fields = ['id', 'name', 'parent_id', 'updated_at'])
    {
        foreach ($trees as $tree) {
            $list[] = Arr::only($tree, $fields);
            self::toList($list, $tree['children'], $fields);
        }
    }

    private static function getTree()
    {
        $tree = Redis::get(self::DepartmentInfos_Prifix . ':Trees');
        if ($tree) {
            $tree = json_decode($tree, true);
        }
        return $tree;
    }

    private static function setAll($departments)
    {
        foreach ($departments as $department) {
            Redis::setex(self::DepartmentInfos_Prifix . ':One:' . $department->id
                , 3600, json_encode($department));
        }
    }

    private static function getRedisOne($id)
    {
        $info = Redis::get(self::DepartmentInfos_Prifix . ':One:' . $id);
        return $info ? json_decode($info, true) : null;
    }

    private static function checkUpdate(): bool
    {
        if (!config('ehr.bg_id', null)) {
            return false;
        }
        if (!Auth::user() || !Auth::user()->ehr_token) {
            return false;
        }
        $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'bg/department/checkUpdate',
            Auth::user()->ehr_token, [
                'group_id' => config('ehr.bg_id'),
            ]);
        $updated_at = $error ? false : $result['updated_at'];
        $old = Redis::get(self::DepartmentInfos_Prifix . ':Updated_at');
        if (!$old || !Carbon::parse($old)->eq($updated_at)) {
            return false;
        }
        return true;
    }
    #endregion

    /**
     * Notes: 获取部门树结构
     * User: harden - 2021/11/8 下午5:03
     * @param $error
     * @param null $department_id 指定部门
     * @return mixed|null
     */
    public static function getTrees(&$error, $department_id = null)
    {
        if (!Auth::user() || !Auth::user()->ehr_token) {
            $error = '请先登录用户！';
            return null;
        }
        $trees = self::getTree();
        if (!$trees || ($trees && !self::checkUpdate())) {
            $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'bg/department/trees',
                Auth::user()->ehr_token, array_merge([
                    'group_id' => config('ehr.bg_id'),
                    'level' => 9,
                ], $department_id ? [
                    'parent_id' => $department_id,
                ] : []));

            if ($error) return null;
            $trees = $result['data']['trees'];
            self::setTree($trees);
        }
        if (!$department_id) {
            return $trees;
        } else {
            return self::searchNode($trees, $department_id);
        }
    }

    private static function searchNode($trees, $department_id)
    {
        foreach ($trees as $tree) {
            if ($tree['id'] == $department_id) {
                return $tree;
            }
            if (sizeof($tree['children']) > 0) {
                $result = self::searchNode($tree['children'], $department_id);
                if ($result) return $result;
            }
        }
        return null;
    }
}
