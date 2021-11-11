<?php


namespace Jxm\Ehr\Auth;


use Carbon\Carbon;
use Hprose\Http\Client;
use Hprose\InvokeSettings;
use Hprose\ResultMode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class EhrAuthRpc
{
    private $client;
    private $allInfos = [];
    private $resultInfos = [];
    private $editorInfos = [];

    const State_Normal = 0;         //正常
    const State_Leave = 10;          //离职
    const State_Black = 20;          //黑名单
    const State_Exception = 30;      //异常

    public function addInfos(&$error, $account, $password, $name, $identity, $sex, $phone, $entry_time,
                             $state, $created_at, $updated_at, $email = null, $address = null, $img = null,
                             $describe = null, $details = null)
    {
        if (sizeof(Arr::where($this->allInfos, function ($item) use ($identity) {
                return $item['identity'] == $identity;
            })) > 0) {
            $error = '该身份证号已存在，不要重复添加！';
            return false;
        }
        $this->allInfos[] = [
            'account' => $account,
            'password' => $password,
            'name' => $name,
            'identity' => $identity,
            'sex' => $sex,
            'phone' => $phone,
            'entry_time' => $entry_time,
            'state' => $state,
            'email' => $email,
            'address' => $address,
            'img' => $img,
            'describe' => $describe,
            'details' => $details,
            'created_at' => Carbon::parse($created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($updated_at)->format('Y-m-d H:i:s'),
        ];
        return true;
    }

    public function sendInfo()
    {
        Log::info('sendInfos', $this->allInfos);
        if (sizeof($this->allInfos) == 0) {
            return null;
        }
        $client = new Client(config('ehr.service') . 'archive/info', false);
        $client->setTimeout(60000);
        $this->resultInfos = $client->import(json_encode($this->allInfos),
            config('ehr.bg_id'),
            new InvokeSettings(['mode' => ResultMode::Normal]));
        return $this->resultInfos;
    }

    public function countInfos()
    {
        return sizeof($this->allInfos);
    }

    public function clearInfos()
    {
        $this->allInfos = [];
        $this->editorInfos = [];
    }

    public function addEditorInfo($identity, $editor_sc_id)
    {
        $this->editorInfos[] = [
            'identity' => $identity,
            'editor_id' => $editor_sc_id,
        ];
    }

    public function updateEditor()
    {
        if (sizeof($this->editorInfos) == 0) {
            return null;
        }
        $client = new Client(config('ehr.service') . 'archive/info', false);
        $res = $client->updateEditor(json_encode($this->editorInfos),
            new InvokeSettings(['mode' => ResultMode::Normal]));
        return $res;
    }
}
