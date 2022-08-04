<?php


namespace Jxm\Ehr;


use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Jxm\Ehr\Model\JxmEhrTokenInfos;

class JxmApi
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
    public static function iam(&$error, $url, $params = [], $tokenInfos = null,
                               $app_id = null, $no_abort = false)
    {
        $host = config('ehr.api');
        return self::postApi($error, $host, $url, $params, $tokenInfos, 'POST', $app_id, $no_abort);
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
    public static function wms(&$error, $url, $params = [], $tokenInfos = null,
                               $app_id = null, $no_abort = false)
    {
        $host = config('ehr.wms');
        return self::postApi($error, $host, $url, $params, $tokenInfos, 'POST', $app_id, $no_abort);
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
    public static function ehr(&$error, $url, $params = [], $tokenInfos = null,
                               $app_id = null, $no_abort = false)
    {
        $host = config('ehr.ehr');
        return self::postApi($error, $host, $url, $params, $tokenInfos, 'POST', $app_id, $no_abort);
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
    public static function order(&$error, $url, $params = [], $tokenInfos = null,
                                 $app_id = null, $no_abort = false)
    {
        $host = config('ehr.oms');
        return self::postApi($error, $host, $url, $params, $tokenInfos, 'POST', $app_id, $no_abort);
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
    public static function postApi(&$error, $host, $url, $params = [], $tokenInfos = null,
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
        if ($tokenInfos == null) {
            $tokenInfos = request()->header('Authorization');
        } elseif ($tokenInfos instanceof JxmEhrTokenInfos) {
            $tokenInfos = $tokenInfos->token_type . ' ' . $tokenInfos->access_token;
        }
        $response = $client->request('POST', $host . $url, [
            'headers' => array_merge([
                'X-Requested-With' => 'XMLHttpRequest',
            ], $tokenInfos ? [
                'Authorization' => $tokenInfos,
            ] : []),
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
