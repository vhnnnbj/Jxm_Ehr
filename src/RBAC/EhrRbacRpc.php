<?php


namespace Jxm\Ehr\RBAC;

use Hprose\Http\Client;
use Hprose\InvokeSettings;
use Hprose\ResultMode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

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
}
