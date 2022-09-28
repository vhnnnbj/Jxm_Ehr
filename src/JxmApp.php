<?php


namespace Jxm\Ehr;


use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class JxmApp
{
    /**
     * Notes: 身份认证系统
     * User: harden - 2022/7/11 上午10:18
     * @param $error
     * @param $url
     * @param array $params
     * @param null $tokenInfos
     * @param null $app_id
     * @param false $no_abort
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function iam(&$error, $url, $params = [],
                               $app_id = null, $no_abort = false, $rt_id = null)
    {
        $host = config('ehr.app');
        return self::postApp($error, $host, $url, $params, 'POST', $app_id, $no_abort, $rt_id);
    }

    /**
     * Notes: 仓储管理系统
     * User: harden - 2022/7/11 上午10:18
     * @param $error
     * @param $url
     * @param array $params
     * @param null $tokenInfos
     * @param null $app_id
     * @param false $no_abort
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function wms(&$error, $url, $params = [],
                               $app_id = null, $no_abort = false, $rt_id = null)
    {
        $host = config('ehr.wms_app');
        return self::postApp($error, $host, $url, $params, 'POST', $app_id, $no_abort, $rt_id);
    }

    /**
     * Notes: 人事业务系统
     * User: harden - 2022/7/11 上午10:18
     * @param $error
     * @param $url
     * @param array $params
     * @param null $tokenInfos
     * @param null $app_id
     * @param false $no_abort
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function ehr(&$error, $url, $params = [],
                               $app_id = null, $no_abort = false, $rt_id = null)
    {
        $host = config('ehr.ehr_app');
        return self::postApp($error, $host, $url, $params, 'POST', $app_id, $no_abort, $rt_id);
    }

    /**
     * Notes: 订单中心
     * User: harden - 2022/7/11 上午10:33
     * @param $error
     * @param $url
     * @param array $params
     * @param null $tokenInfos
     * @param null $app_id
     * @param false $no_abort
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function oms(&$error, $url, $params = [],
                               $app_id = null, $no_abort = false, $rt_id = null)
    {
        $host = config('ehr.oms_app');
        return self::postApp($error, $host, $url, $params, 'POST', $app_id, $no_abort . $rt_id);
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
    public static function postApp(&$error, $host, $url, $params = [],
                                   $method = 'POST', $app_id = null, $no_abort = false, $rt_id = null)
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
        $response = $client->request('POST', $host . $url, [
            'headers' => array_merge([
                'X-Requested-With' => 'XMLHttpRequest',
            ], $rt_id ? [
                'rt_request_id' => session_create_id(),
                'rt_transact_id' => $transact_id,
            ] : []),
            'form_params' => $params,
        ]);
        if ($response->getStatusCode() != 200) {
            $result = JxmEsb::errMsg($params['api_track_msg_id']);

            $error = $result['msg'] ?? '';
            $result['message'] = $error;
            if (!$no_abort) {
                abort($result['code'] ?? 500, $result['msg'] ?? '');
            }
        } else {
            $result = json_decode($response->getBody()->getContents(), true);
        }
        return $result;
    }
}
