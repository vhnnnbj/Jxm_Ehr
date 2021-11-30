<?php


namespace Jxm\Ehr\Model;


class AppInfo extends EhrBasicModel
{
    /**
     * 集团应用
     * 不关联BG
     * 使用用户档案(UserInfo)所有角色
     */
    const Type_System = 10;
    const Type_Platform = 20;
    const Type_Customize = 30;
}
