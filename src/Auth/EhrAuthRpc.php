<?php


namespace Jxm\Ehr\Auth;


use App\Models\User;
use Hprose\Http\Client;
use Hprose\InvokeSettings;
use Hprose\ResultMode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class EhrAuthRpc
{
    public $client;
    public $allInfos = [];

    public function addInfos(&$error, $account, $password, $name, $identify, $sex, $phone, $entry_time,
                             $address = null, $img = null, $describe = null, $editor_id = null, $details = null)
    {
        if (sizeof(Arr::where($this->allInfos, function ($item) use ($identify) {
                return $item['identify'] == $identify;
            })) > 0) {
            $error = makeErrorMsg('该身份证号已存在，不要重复添加！', 5);
            return false;
        }
        $this->allInfos[] = [
            'account' => $account,
            'password' => $password,
            'name' => $name,
            'identify' => $identify,
            'sex' => $sex,
            'phone' => $phone,
            'entry_time' => $entry_time,
            'address' => $address,
            'img' => $img,
            'describe' => $describe,
            'editor_id' => $editor_id,
            'details' => $details,
        ];
        return true;
    }

    public function sendInfo()
    {

    }

    public function init()
    {
        $client = new Client(config('ehr.url') . '/archive/info', false);
        $allCount = UserInfo::whereNull('sc_id')->count();
        $success = $fail = 0;
        $send = 0;
        $users = User::get()->makeVisible(['id', 'password', 'name', 'created_at', 'updated_at', 'deleted_at', 'userinfo_id']);
        $infos = UserInfo::with(['editor:id,sc_id,identityNumber'])->whereNull('sc_id')->where('id', '!=', 1)
            ->get();
        $infos->map(function ($item) use ($users) {
            $item->account = $users->where('userinfo_id', $item->id)->first();
            if (!is_null($item->account)) $item->account = $item->account->toArray();
        });
        Log::info('infos' . $success, $infos->toArray());
        $res = $client->import(json_encode($infos->toArray()), new InvokeSettings(['mode' => ResultMode::Normal]));

        echo json_encode($res) . "\r\n";
        Log::info('result:' . $send, $res);

        foreach ($res['success'] as $item) {
            UserInfo::where('id', $item['userinfo_id'])->update([
                'sc_id' => $item['sc_id'],
            ]);
        }
        foreach ($res['fail'] as $item) {
            if ($item['type'] == 'exists') {
                UserInfo::where('id', $item['userinfo_id'])->update([
                    'sc_id' => $item['sc_id'],
                ]);
            }
        }
        $infos = UserInfo::with(['editor:id,sc_id'])->where('id', '!=', 1)
            ->get(['id', 'editor_id', 'username', 'identityNumber', 'sc_id']);
        $res = $client->updateEditor(json_encode($infos->toArray()), new InvokeSettings(['mode' => ResultMode::Normal]));
        Log::info('update result', $res);
    }

    public function addInfo()
    {

    }

}
