<?php

use Illuminate\Support\Facades\Auth;
use Jxm\Ehr\Model\JxmEhrTokenInfos;
use Jxm\Ehr\JxmEhrAccessHelper;
use Illuminate\Support\Facades\Redis;

class EhrBgApi
{
    #region Redis BgGroups
    const Prefix_BgGroups = 'Jxm:Ehr:BgGroups:';
    const Infix_List = 'List';
    const Infix_One = 'One:';

    private static function setBgs($bgs)
    {
        Redis::setex(self::Prefix_BgGroups . self::Infix_List, 3600, json_encode($bgs));
        foreach ($bgs as $bg) {
            Redis::setex(self::Prefix_BgGroups . self::Infix_One . $bg->id, 3600, json_encode($bg));
        }
    }

    private static function updateBgs($bgs)
    {
        
        $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'bg/bg/groups', $token,
            [
                'page' => 1,
                'limit' => 50,
                'app_id' => $app_id,
            ]);
        $info = $result['data']['list'];

    }

    #endregion

    public static function getBgs(JxmEhrTokenInfos $token, string $app_id)
    {
        foreach ()
    }
}
