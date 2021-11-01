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
    private $editorInfos = [];
    private $menu_keys = ['id', 'name', 'url', 'icon', 'sort', 'path', 'editor_ehr_id',
        'children', 'resources'];
    private $res_keys = ['id', 'name', 'type'];

    public function addMenus(&$error, $allMenus): bool
    {
        foreach ($allMenus as $menu) {
            $this->checkMenu($error, $menu);
            if ($error) return false;
        }
        $this->menuInfos = $allMenus;
        return true;
    }

    private function checkMenu(&$error, $menu)
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
    }

    public function sendMenus(&$error)
    {
        Log::info('sendInfos', $this->menuInfos);
        if (sizeof($this->menuInfos) == 0) {
            $error = '没有已添加的菜单信息！';
            return null;
        }
        if (config('ehr.bg_id') == 0 || config('ehr.app_id') == '0') {
            $error = '请完善BG和APP的信息！';
            return null;
        }
        $client = new Client(config('ehr.service') . 'rbac/rbac', false);
        $client->setTimeout(60000);
        $this->resultInfos = $client->importMenuInfo(json_encode($this->allInfos),
            config('ehr.bg_id'), config('ehr.app_id'),
            new InvokeSettings(['mode' => ResultMode::Normal]));
        return $this->resultInfos;
    }
    #endregion

    #region Roles
    public function addRoles($roleInfos)
    {

    }
    #endregion
}
