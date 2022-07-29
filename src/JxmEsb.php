<?php


namespace Jxm\Ehr;


use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class JxmEsb
{
    public static function get($after = null, $except_ids = null)
    {
        $app_id = config('ehr.app_id', '');
        if (!$app_id) {
            $error = '请正确配置APPID!';
            return false;
        }
        $params = [
            'app_id' => config('ehr.app_id', ''),
            'after' => $after ?: '2022-07-01 00:00:00',
            'except_ids' => $except_ids,
        ];
        $reuslt = self::postApi($error, config('ehr.esb') . 'esb/get', $params);
        if ($error) {
            return false;
        }
        return $result['msgs'];
    }

    public static function set(&$error, $module, $type, $subtype, $infos = null, $editor_id = null, $app_id = null)
    {
//        'app_id' => 'required',
//            'module' => 'required|string',
//            'type' => 'required|string',
//            'subtype' => 'required|string',
//            'infos' => 'nullable|json',
//            'editor_id' => 'required|integer',
        $app_id = config('ehr.app_id', '');
        if (!$app_id) {
            $error = '请正确配置APPID!';
            return false;
        }
        $params = [
            'app_id' => config('ehr.app_id', ''),
            'module' => $module,
            'type' => $type,
            'subtype' => $subtype,
            'infos' => $infos,
            'editor_id' => $editor ? 0,
        ];
        self::postApi($error, config('ehr.esb') . 'esb/set', $params);
        return $error ? false : true;
    }

    public static function postApi(&$error, $url, $params = [],
                                   $method = 'POST', $app_id = null, $no_abort = false)
    {
        $response = null;
        if (!$app_id && !array_key_exists('app_id', $params)) {
            $app_id = config('ehr.app_id', null);
            if (!$app_id) {
                $error = '需要提供appid才可访问!';
                return null;
            }
            $params = array_merge($params, [
                'app_id' => $app_id,
            ]);
        }
        $params['api_track_msg_id'] = random_int(1000000, 9999999);

        $client = new Client(['http_errors' => false]);
        $response = $client->request('POST', config('ehr.esb') . $url, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
            ],
            'form_params' => $params,
        ]);
        if ($response->getStatusCode() != 200) {
            $response = $client->post($host . 'helper/getErrorMessage', [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
                'form_params' => Arr::only($params, 'api_track_msg_id'),
            ]);
            if ($response->getStatusCode() != 200) {
                $result['code'] = 500;
                $result['msg'] = '未提供的错误信息！';
                if (!$no_abort) {
                    abort($result['code'], $result['msg']);
                }
                return $result;
            }
            $result = json_decode($response->getBody()->getContents(), true);
            $result['code'] = $result['code'] ?? 500;
            $result['msg'] = $result['msg'] ?? '未知错误！';
            $error = $result['msg'];
            $result['message'] = $error;
            if (!$no_abort) {
                abort($result['code'], $result['msg']);
            }
        } else {
            $result = json_decode($response->getBody()->getContents(), true);
        }
        return $result;
    }

}
