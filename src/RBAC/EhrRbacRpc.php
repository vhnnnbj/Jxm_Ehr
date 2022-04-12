<?php


namespace Jxm\Ehr\RBAC;

use Hprose\Http\Client;
use Hprose\InvokeSettings;
use Hprose\ResultMode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Jxm\Ehr\JxmEhrAccessHelper;

class EhrRbacRpc
{
    private $client;

    #region Menus
    private $menuInfos = [];
    private $menu_keys = ['id', 'name', 'url', 'icon',
        'sort', 'path', 'editor_ehr_id',
        'children', 'resources'];
    private $res_keys = ['id', 'name'];

    public function addMenus(&$error, array $allMenus): bool
    {
        foreach ($allMenus as $menu) {
            $this->checkMenu($error, $menu);
            if ($error) return false;
        }
        $this->menuInfos = $allMenus;
        return true;
    }

    private function checkMenu(&$error, array $menu): bool
    {
        if (!Arr::has($menu, $this->menu_keys)) {
            $error = '菜单信息必须包含以下：' . join(',', $this->menu_keys) . ',请确认后重新添加！';
            return false;
        }
        foreach ($menu['children'] as $child) {
            $this->checkMenu($error, $child);
            if ($error) return false;
        }
        foreach ($menu['resources'] as $resource) {
            if (!Arr::has($resource, $this->res_keys)) {
                $error = '资源信息必须包含以下字段：' . join(',', $this->res_keys) . ',请确认后重新添加！';
                return false;
            }
        }
        return true;
    }

    public function sendMenus(&$error, $app_id = '')
    {
        Log::info('sendInfos', $this->menuInfos);
        if (sizeof($this->menuInfos) == 0) {
            $error = '没有已添加的菜单信息！';
            return null;
        }
        $app_id = $app_id ?: config('ehr.app_id');
        if (config('ehr.bg_id') == '' || $app_id == '') {
            $error = '请完善BG和APP的信息！';
            return null;
        }
        try {
            $client = new Client(config('ehr.service') . 'rbac/rbac', false);
            $this->resultInfos = $client->importMenuInfo(json_encode($this->menuInfos),
                config('ehr.bg_id'), $app_id,
                new InvokeSettings(['mode' => ResultMode::Normal]));
            return $this->resultInfos;
        } catch (\Exception $ex) {
            return [
                'code' => 10,
                'msg' => $ex->getMessage(),
                'trace' => $ex->getTrace(),
            ];
        }
    }
    #endregion

    #region Roles
    private $role_keys = ['id', 'name', 'app_id', 'permission_ehr_ids', 'ehr_id'];
    private $roleInfos = [];

    public function addRoles($id, $name, $type, $menu_ehr_ids, $res_ehr_ids, $app_id = null, $ehr_id = null)
    {
        $this->roleInfos[] = [
            'id' => $id,
            'name' => $name,
            'app_id' => $app_id,
            'type' => $type,
            'menu_ehr_ids' => $menu_ehr_ids,
            'res_ehr_ids' => $res_ehr_ids,
            'ehr_id' => $ehr_id
        ];
        return sizeof($this->roleInfos);
    }

    public function sendRoles(&$error)
    {
        Log::info('importRolePermissionInfos', $this->roleInfos);
        if (sizeof($this->roleInfos) == 0) {
            $error = '没有已添加的菜单信息！';
            return null;
        }
        if (config('ehr.bg_id') == '') {
            $error = '请完善BG信息！';
            return null;
        }
        try {
            $client = new Client(config('ehr.service') . 'rbac/rbac', false);
            $this->resultInfos = $client->importRolePermissionInfos(json_encode($this->roleInfos),
                config('ehr.bg_id'), new InvokeSettings(['mode' => ResultMode::Normal]));
            return $this->resultInfos;
        } catch (\Exception $ex) {
            return [
                'code' => 10,
                'msg' => $ex->getMessage(),
            ];
        }
    }
    #endregion

    #region Static Functions
    const ScopeType_Normal = 0;
    const ScopeType_Company = 1;

    public static function menuScope(&$error, $menus, $user_id, $resource = null, $bg_id = null,
                                     $department_id = null, $withStop = true, $type = self::ScopeType_Normal)
    {
        $permission = JxmEhrAccessHelper::rpc('rbac', 'rbac', 'getPermissionByName', [
            $error, $menus, config('ehr.app_id'), 1, $resource
        ]);
        if (!isset($permission['id'])) {
            $error = '权限不存在,请联系技术支持处理！';
        }
        return self::getScopes($error, $permission['id'], $bg_id, $department_id, $withStop, $user_id);
    }

    public static function moduleScope(&$error, $modules, $user_id, $operate, $bg_id = null,
                                       $department_id = null, $withStop = true, $type = self::ScopeType_Normal)
    {
        $permission = JxmEhrAccessHelper::rpc('rbac', 'rbac', 'getPermissionByName', [
            $error, $modules, config('ehr.app_id'), 2, $operate
        ]);
        if (!isset($permission['id'])) {
            $error = '权限不存在,请联系技术支持处理！';
            return null;
        }
        return self::getScopes($error, $permission['id'], $bg_id, $department_id, $withStop, $user_id);
    }

    public static function getScopes(&$error, $permission, $bg_id = null,
                                     $department_id = null, $withStop = true, $userinfo_id = null)
    {
        $result = JxmEhrAccessHelper::rpc('rbac', 'rbac', 'getScopes', [
            $error, $permission, $bg_id,
            $department_id, $withStop, $userinfo_id
        ]);
        return $error ? null : $result;
    }
    #endregion
}
