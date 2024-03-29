<?php


namespace Jxm\Ehr;


use GuzzleHttp\Client;
use Jxm\Ehr\Model\JxmEhrTokenInfos;
use Laravel\ResetTransaction\Facades\RT;

//use Laravel\ResetTransaction\Facades\RT;

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
                               $app_id = null, $no_abort = false, $rt_id = null)
    {
        $host = config('ehr.api');
        return self::postApi($error, $host, $url, $params, $tokenInfos, 'POST', $app_id, $no_abort, $rt_id);
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
                               $app_id = null, $no_abort = false, $rt_id = null)
    {
        $host = config('ehr.wms');
        return self::postApi($error, $host, $url, $params, $tokenInfos, 'POST', $app_id, $no_abort, $rt_id);
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
                               $app_id = null, $no_abort = false, $rt_id = null)
    {
        $host = config('ehr.ehr');
        return self::postApi($error, $host, $url, $params, $tokenInfos, 'POST', $app_id, $no_abort, $rt_id);
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
    public static function oms(&$error, $url, $params = [], $tokenInfos = null,
                               $app_id = null, $no_abort = false, $rt_id = null)
    {
        $host = config('ehr.oms');
        return self::postApi($error, $host, $url, $params, $tokenInfos, 'POST', $app_id, $no_abort, $rt_id);
    }

    /**
     * Notes: ESB
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
    public static function esb(&$error, $url, $params = [], $tokenInfos = null,
                               $app_id = null, $no_abort = false, $rt_id = null)
    {
        $host = config('ehr.esb');
        return self::postApi($error, $host, $url, $params, $tokenInfos, 'POST', $app_id, $no_abort, $rt_id);
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
        if ($tokenInfos == null) {
            $tokenInfos = request()->header('Authorization');
        } elseif ($tokenInfos instanceof JxmEhrTokenInfos) {
            $tokenInfos = $tokenInfos->token_type . ' ' . $tokenInfos->access_token;
        }
        if (!$rt_id) {
            $rt_id = RT::getTransactId();
        }
        $response = $client->request('POST', $host . $url, [
            'headers' => array_merge([
                'X-Requested-With' => 'XMLHttpRequest',
            ], $rt_id ? [
                'rt-request-id' => session_create_id(),
                'rt-transact-id' => $rt_id,
            ] : [], $tokenInfos ? [
                'Authorization' => $tokenInfos,
            ] : []),
            'form_params' => $params,
        ]);
        if ($response->getStatusCode() != 200) {
            $result = JxmEsb::errMsg($params['api_track_msg_id']);
            $error = $result['msg'];
            if (!$no_abort) {
                abort($result['code'] ?? 500, $result['msg'] ?? '');
            }
        } else {
            $result = json_decode($response->getBody()->getContents(), true);
            if (isset($result['code']) && $result['code'] != 0) {
                $error = makeErrorMsg($result['msg'] ?? '', $result['code']);
            }
        }
        return $result;
    }
}
