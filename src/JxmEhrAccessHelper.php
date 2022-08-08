<?php


namespace Jxm\Ehr;


use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Jxm\Ehr\Model\JxmEhrTokenInfos;
use Yaf\Response\Cli;

class JxmEhrAccessHelper
{
    public static function validate(&$error, $infos, $rules, $msgs = null,
                                    $attributes = null): bool
    {
        $result = self::api($error, 'helper/checkData', null, [
            'rules' => $rules,
            'infos' => $infos,
            'msg' => $msgs,
            'attributes' => $attributes,
        ]);
        if ($error) {
            return false;
        }
        if ($result['code'] == 422) {
            $error = $result;
            return false;
        } else {
            return true;
        }
    }

    /**
     * Notes:
     * User: harden - 2022/2/24 上午10:34
     * @param $error
     * @param $url
     * @param $tokenInfos
     * @param array $params
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function api(&$error, $url, $tokenInfos, $params = [], $no_abort = false)
    {
        return self::postApi($error, config('ehr.api') . $url, $tokenInfos, $params, 'POST', null, $no_abort);
    }

    /**
     * Notes: api访问
     * User: harden - 2021/11/19 上午9:51
     * @param string $error
     * @param $url
     * @param JxmEhrTokenInfos|null $tokenInfos
     * @param array $params
     * @param string $method
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function postApi(&$error, $url, $tokenInfos, $params = [],
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
        if (!$tokenInfos) {
            $tokenInfos = request()?->header('Authorization');
        }

        $client = new Client(['http_errors' => false]);
        $response = $client->request('POST', $url, [
            'headers' => array_merge([
                'X-Requested-With' => 'XMLHttpRequest',
            ], $tokenInfos ? [
                'Authorization' => ($tokenInfos instanceof JxmEhrTokenInfos) ? ($tokenInfos->token_type . ' ' . $tokenInfos->access_token) : $tokenInfos,
            ] : []),
            'form_params' => $params,
        ]);
        if ($response->getStatusCode() != 200) {
            $response = $client->post(config('ehr.api') . 'helper/getErrorMessage', [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
                'form_params' => Arr::only($params, 'api_track_msg_id'),
            ]);
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

    public static function rpc(string $module, string $class, string $method, array $args)
    {
        $client = new \Hprose\Http\Client(config('ehr.service') . $module . '/helper/' . $class, false);
        $client->byref = true;
        $result = $client->invoke($method, $args);
        return $result;
    }

    #region data connection
    public static function getConn()
    {
        $conn = Cache::get('ehr.data.connection.config', null);
        if (!$conn) {
            $token = JxmEhrTokenInfos::where('user_id', Auth::user()->id)->first();
            $result = JxmEhrAccessHelper::api($error, 'helper/dataConn', $token);
            if ($error) {
                abort(403);
            }
            $conn = json_encode($result['mysql']);
            Cache::set('ehr.data.connection.config', $conn, 30);
        }
        return json_decode($conn, true);
    }
    #endregion
}
