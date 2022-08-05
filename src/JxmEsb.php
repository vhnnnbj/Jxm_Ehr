<?php


namespace Jxm\Ehr;


use GuzzleHttp\Client;

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
        $reuslt = self::postApi($error, 'esb/get', $params);
        if ($error) {
            return false;
        }
        return $result['msgs'] ?? [];
    }

    public static function set(&$error, $name, $value, $route = null,
                               $infos = null, $editor_id = null, $app_id = null)
    {
        $app_id = config('ehr.app_id', '');
        if (!$app_id) {
            $error = '请正确配置APPID!';
            return false;
        }
        $params = [
            'app_id' => $app_id,
            'route' => $route,
            'name' => $name,
            'value' => $value,
            'infos' => $infos,
            'editor_id' => $editor_id ?: 0,
        ];
        self::postApi($error, 'esb/set', $params);
        return $error ? false : true;
    }

    /**
     * Notes: $msg为空时获取错误信息，不为空时设置错误信息
     * User: harden - 2022/8/1 下午5:08
     * @param $track_id
     * @param null $msg
     * @param null $obj
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function errMsg($track_id, $code = null, $msg = null, $obj = null, $app_id = null)
    {
        $url = config('ehr.esb') . 'esb/errMsg';
        $params['track_id'] = $track_id;
        $params['app_id'] = $app_id ?: config('ehr.app_id', '');
        if ($msg) {
            $params['code'] = $code;
            $params['msg'] = $msg;
            $params['obj'] = $obj;
        }
        $client = new Client(['http_errors' => false]);
        $response = $client->request('POST', $url, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
            ],
            'form_params' => $params,
        ]);
        if ($msg) {
            return true;
        } else {
            if ($response->getStatusCode() == 200) {
                $str = urldecode($response->getBody()->getContents());
                $result = json_decode($str, true)['err'] ?: [
                    'obj' => null,
                    'msg' => '未知的错误信息！',
                    'code' => 500,
                ];
            } else {
                $result = [
                    'obj' => null,
                    'msg' => '未知的错误信息！',
                    'code' => 500,
                ];
            }
            return $result;
        }
    }

    public static function postApi(&$error, $url, $params = [],
                                   $method = 'POST', $app_id = null, $no_abort = false)
    {
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
            $result = self::errMsg($params['api_track_msg_id']);
            if (!$no_abort) {
                abort($result['code'], $result['msg']);
            }
        } else {
            $result = json_decode($response->getBody()->getContents(), true);
        }
        return $result;
    }

}
