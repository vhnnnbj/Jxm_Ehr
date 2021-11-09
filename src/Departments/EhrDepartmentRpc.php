<?php


namespace Jxm\Ehr\Departments;


use Hprose\Http\Client;
use Hprose\InvokeSettings;
use Hprose\ResultMode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Array_;

class EhrDepartmentRpc
{
    private $client;

    #region Department
    private $departmentInfos = [];
    private $department_keys = ['id', 'name', 'describe', 'children', 'editor_ehr_id', 'address',
        'subway', 'bus_routes'];

    public function setDepartments(&$error, array $allDeparts)
    {
        foreach ($allDeparts as $depart) {
            $this->checkDepartment($error, $depart);
            if ($error) return false;
        }
        $this->departmentInfos = $allDeparts;
    }

    private function checkDepartment(&$error, $department)
    {
        if (!is_array($department)) $department = $department->toArray();
        if (!Arr::has($department, $this->department_keys)) {
            $error = '菜单信息必须包含以下：' . join(',', $this->department_keys) . ',请确认后重新添加！' .
                json_encode($department);
            return false;
        }
        foreach ($department['children'] as $child) {
            $this->checkDepartment($error, $child);
            if ($error) return false;
        }
    }

    public function sendDepartment(&$error, $app_id = null)
    {
        Log::info('sendInfos', $this->departmentInfos);
        if (sizeof($this->departmentInfos) == 0) {
            $error = '没有已添加的部门信息！';
            return null;
        }
        $app_id = $app_id ?: config('ehr.app_id');
        if (config('ehr.bg_id') == '' || $app_id == '') {
            $error = '请完善BG和APP的信息！';
            return null;
        }
        try {
            $client = new Client(config('ehr.service') . 'bg/department', false);
            $this->resultInfos = $client->importDepartments(json_encode($this->departmentInfos),
                $app_id, config('ehr.bg_id'),
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

    #region Positions
    private $positionInfos = [];
    private $position_keys = ['id', 'userinfo_ehr_id', 'department_ehr_id', 'editor_ehr_id', 'role_ehr_ids'];

    public function setPositions(&$error, $allInfos)
    {
        foreach ($allInfos as $info) {
            if (!Arr::has($info, $this->position_keys)) {
                $error = '职位信息必须包含以下：' . join(',', $this->position_keys) . ',请确认后重新添加！';
                return false;
            }
        }
        $this->positionInfos = $allInfos;
        return true;
    }

    public function sendPositions(&$error, $app_id)
    {
        Log::info('sendPositions', $this->positionInfos);
        if (sizeof($this->positionInfos) == 0) {
            $error = '没有已添加的职责信息！';
            return null;
        }
        $app_id = $app_id ?: config('ehr.app_id');
        if (config('ehr.bg_id') == '' || $app_id == '') {
            $error = '请完善BG和APP的信息！';
            return null;
        }
        try {
            $client = new Client(config('ehr.service') . 'bg/department', false);
            $this->resultInfos = $client->importLocations(json_encode($this->positionInfos),
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
}
