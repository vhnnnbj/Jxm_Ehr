<?php


namespace Jxm\Ehr;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Jxm\Ehr\Departments\EhrDepartmentApi;
use Jxm\Ehr\User\EhrUserApi;

trait HasEhrInfos
{
    private static function ehrKeys(): array
    {
        return [
            'department_id' => EhrCode::EhrKey_Department,
            'editor_id' => EhrCode::EhrKey_UserInfo,
        ];
    }

    public static function setEhrList(&$list)
    {
        return self::setEhrInfos($list, self::ehrKeys());
    }

    public static function setOneEhr(&$item)
    {
        return self::setOneEhrInfo($item, self::ehrKeys());
    }

    private static function setEhrInfos(Collection &$list, $keys = null)
    {
        if (!$keys) $keys = self::ehrKeys();
        foreach (array_keys($keys) as $key) {
            switch ($keys[$key]) {
                case 5:
                    $department_ids = $list->pluck($key)->toArray();
                    $departments = EhrDepartmentApi::getDepartments($department_ids);
                    $list->transform(function ($item) use ($departments, $key) {
                        $info = Arr::first($departments, function ($department) use ($item, $key) {
                            return $department['id'] == $item[$key];
                        });
                        $item->setRelation(explode('_', $key)[0],
                            $info ? collect($info) : null);
                        return $item;
                    });
                    break;
                case 6:
                    $user_ids = $list->pluck($key)->toArray();
                    $users = EhrUserApi::getUsers($user_ids);
                    $list->transform(function ($item) use ($users, $key) {
                        $info = Arr::first($users, function ($user) use ($item, $key) {
                            return $user['id'] == $item[$key];
                        });
                        $item->setRelation(explode('_', $key)[0],
                            $info ? collect($info) : null);
                        return $item;
                    });
                    break;
            }
        }
    }

    private static function setOneEhrInfo(&$item, $keys = null)
    {
        if (!$keys) $keys = self::ehrKeys();
        foreach (array_keys($keys) as $key) {
            if (is_array($keys[$key])) {
                switch ($keys[$key][0]) {
                    case 5:
                        $department_ids = [$item[$key]];
                        $departments = EhrDepartmentApi::getDepartments($department_ids, $keys[$key][1]);
                        $info = Arr::first($departments, function ($department) use ($item, $key) {
                            return $department['id'] == $item[$key];
                        });
                        $item->setRelation(explode('_', $key)[0],
                            $info ? collect($info) : null);
                        break;
                    case 6:
                        $user_ids = [$item[$key]];
                        $users = EhrUserApi::getUsers($user_ids, $keys[$key][1]);
                        $info = Arr::first($users, function ($user) use ($item, $key) {
                            return $user['id'] == $item[$key];
                        });
                        $item->setRelation(explode('_', $key)[0],
                            $info ? collect($info) : null);
                        break;
                }
            } else {
                switch ($keys[$key]) {
                    case 5:
                        $department_ids = [$item[$key]];
                        $departments = EhrDepartmentApi::getDepartments($department_ids);
                        $info = Arr::first($departments, function ($department) use ($item, $key) {
                            return $department['id'] == $item[$key];
                        });
                        $item->setRelation(explode('_', $key)[0],
                            $info ? collect($info) : null);
                        break;
                    case 6:
                        $user_ids = [$item[$key]];
                        $users = EhrUserApi::getUsers($user_ids);
                        $info = Arr::first($users, function ($user) use ($item, $key) {
                            return $user['id'] == $item[$key];
                        });
                        $item->setRelation(explode('_', $key)[0],
                            $info ? collect($info) : null);
                        break;
                }
            }
        }
    }

}
