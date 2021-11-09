<?php

use Illuminate\Support\Facades\Cache;

class EhrUserApi
{
    const Prefix_UserInfo = 'Jxm:Ehr:UserInfo:';
    const Infix_One = 'One:';

    public static function cacheAll($users)
    {
        foreach ($users as $user) {
            Cache::put(self::Prefix_UserInfo . self::Infix_One . $user['id'],
                json_encode($user), 60);
        }
    }

    public static function getUsers($user_ids)
    {
        $users = [];
        $none = [];
        foreach ($user_ids as $user_id) {
            $user = self::getOne($user_id);
            if ($user)
                $users[] = $user;
            else
                $none[] = $user_id;
        }
        if ($sizeof($none) > 0) {
            $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'archive/account/someInfos',
                Auth::user()->ehr_token, [
                    'user_ids' => join(',', $none),
                ]);
            if ($error) throw new \Exception($error);
            $get_users = $result['data']['infos'];
            self::cacheAll($get_users);
            $users = array_merge($users, $get_users);
        }
        return $users;

    }

    public static function getOne($id)
    {
        $user = Cache::get(self::Prefix_UserInfo . self::Infix_One . $id);
        return $user ? json_decode($user, true) : null;
    }
}
