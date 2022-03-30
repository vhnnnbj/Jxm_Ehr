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

    private static function canMenu(&$error, JxmEhrTokenInfos $tokenInfos, $menu_name, $app_id = 0, $department_id = 0, $resource = null)
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

    public static function operate(&$error, JxmEhrTokenInfos $tokenInfos, $module_name, $operate, $department_id = null, $withUpgrades = false)
    {
        return self::canOperate($error, $tokenInfos, $module_name, $operate, config('ehr.app_id'), $department_id, $withUpgrades);
    }

    private static function canOperate(&$error, JxmEhrTokenInfos $tokenInfos, $module_name, $operate, $app_id,
                                       $department_id = null, $withUpgrades = false)
    {
        $result = JxmEhrAccessHelper::api($error, 'rbac/rbac/canOperate', $tokenInfos, [
            'app_id' => $app_id,
            'module_names' => $module_name,
            'operate' => $operate,
            'department_id' => $department_id,
            'withUpgrades' => $withUpgrades,
        ]);
        if ($error) return false;
        if ($result['code'] != 0) {
            $error = $result['msg'];
            return false;
        }
        return true;
    }

    public static function menuUrl(&$error, JxmEhrTokenInfos $tokenInfos, $url, $app_id = null, $resource = null,
                                   $bg_id = null, $department_id = null, $withUpgrades = false)
    {
        return self::canMenuUrl($error, $tokenInfos, $url, $app_id ?: config('ehr.app_id'),
            $resource, $bg_id ?: config('ehr.bg_id'), $department_id, $withUpgrades);
    }

    /**
     * Notes: 根据url判断菜单权限
     * User: harden - 2021/12/7 上午10:26
     * @param $error
     * @param JxmEhrTokenInfos $tokenInfos
     * @param $url
     * @param $app_id
     * @param $resource
     * @param $bg_id
     * @param $department_id
     * @param bool $withUpgrades
     * @return bool
     */
    private static function canMenuUrl(&$error, JxmEhrTokenInfos $tokenInfos, $url, $app_id, $resource,
                                       $bg_id, $department_id, $withUpgrades = false)
    {
        $result = JxmEhrAccessHelper::api($error, 'rbac/rbac/canMenuUrl', $tokenInfos, [
            'app_id' => $app_id,
            'url' => $url,
            'bg_id' => $bg_id,
            'resource' => $resource,
            'department_id' => $department_id,
            'withUpgrades' => $withUpgrades,
        ]);
        if ($error) return false;
        if ($result['code'] != 0) {
            $error = $result['msg'];
            return false;
        }
        return true;
    }

    public static function can(&$error, JxmEhrTokenInfos $tokenInfos, $permission_id, $bg_id = null, $department_id = null)
    {
        $result = JxmEhrAccessHelper::api($error, 'rbac/rbac/can', $tokenInfos, [
            'bg_id' => $bg_id ?: config('ehr.bg_id'),
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
            'bg_id' => $bg_id ?: config('ehr.bg_id'),
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
