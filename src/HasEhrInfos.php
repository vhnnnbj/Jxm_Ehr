<?php


namespace Jxm\Ehr;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Jxm\Ehr\Departments\EhrDepartmentApi;
use Jxm\Ehr\User\EhrUserApi;

trait HasEhrInfos
{
//    public $key_types = [
//        'department' => 5,
//        'user' => 6,
//        'bg' => 7,
//        'app' => 8,
//    ];

    abstract public function setEhrInfo(&$list);

    public static function setEhrInfos(Collection &$list, $keys = [
        'department_id' => 5,
        'editor_id' => 6,
    ])
    {
        foreach (array_keys($keys) as $key) {
            switch ($keys[$key]) {
                case 5:
                    $department_ids = $list->pluck($key)->toArray();
                    $departments = EhrDepartmentApi::getDepartments($department_ids);
                    $list->transform(function ($item) use ($departments, $key) {
                        $item->setRelation(explode('_', $key)[0],
                            collect(Arr::first($departments, function ($department) use ($item, $key) {
                                return $department['id'] == $item[$key];
                            })));
                        return $item;
                    });
                    break;
                case 6:
                    $user_ids = $list->pluck($key)->toArray();
                    $users = EhrUserApi::getUsers($user_ids);
                    $list->transform(function ($item) use ($users, $key) {
                        $item->setRelation(explode('_', $key)[0],
                            collect(Arr::first($users, function ($user) use ($item, $key) {
                                return $user['id'] == $item[$key];
                            })));
                        return $item;
                    });
                    break;
            }
        }
    }

}
