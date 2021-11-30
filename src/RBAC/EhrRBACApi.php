<?php


namespace Jxm\Ehr\RBAC;


use Jxm\Ehr\JxmEhrAccessHelper;
use Jxm\Ehr\Model\JxmEhrTokenInfos;

class EhrRBACApi
{
    #region Check Permissions
    public static function menu(&$error, JxmEhrTokenInfos $tokenInfos, $menu_name, $department_id = 0, $resource = null)
    {
        return self::canMenu($error, $tokenInfos, $menu_name, config('ehr.app_id'), $department_id, $resource);
    }

    public static function canMenu(&$error, JxmEhrTokenInfos $tokenInfos, $menu_name, $app_id = 0, $department_id = 0, $resource = null)
    {
        $result = JxmEhrAccessHelper::api($error, 'rbac/rbac/canMenu', $tokenInfos, [
            'app_id' => $app_id,
            'menu_names' => $menu_name,
            'resource' => $resource,
            'department_id' => $department_id,
        ]);
        if ($error) return false;
        if ($result['code'] != 0) {
            $error = $result['msg'];
            return false;
        }
        return true;
    }

    public static function operate(&$error, JxmEhrTokenInfos $tokenInfos, $module_name, $operate, $department_id = 0)
    {
        return self::canOperate($error, $tokenInfos, $module_name, $operate, config('ehr.app_id'), $department_id);
    }

    public static function canOperate(&$error, JxmEhrTokenInfos $tokenInfos, $module_name, $operate, $app_id = 0, $department_id = 0)
    {
        $result = JxmEhrAccessHelper::api($error, 'rbac/rbac/canOperate', $tokenInfos, [
            'app_id' => $app_id,
            'module_names' => $module_name,
            'operate' => $operate,
            'department_id' => $department_id,
        ]);
        if ($error) return false;
        if ($result['code'] != 0) {
            $error = $result['msg'];
            return false;
        }
        return true;
    }

    public static function can(&$error, JxmEhrTokenInfos $tokenInfos, $department_id, $permission_id)
    {
        $result = JxmEhrAccessHelper::api($error, 'rbac/rbac/can', $tokenInfos, [
            'department_id' => $department_id,
            'permission_id' => $permission_id,
        ]);
        if ($error) return false;
        if ($result['code'] != 0) {
            $error = $result['msg'];
            return false;
        }
        return true;
    }
    #endregion

    #region Rbac Infos
    public static function ownRoles(&$error, JxmEhrTokenInfos $tokenInfos, $bg_id = null)
    {
        $result = JxmEhrAccessHelper::api($error, 'rbac/role/own', $tokenInfos, [
            'page' => 1,
            'limit' => 200,
            'bg_id' => $bg_id,
        ]);
        if ($error) return null;
        if ($result['code'] != 0) {
            $error = $result['msg'];
            return null;
        }
        return $result['data']['list'];
    }
    #endregion
}
