<?php


namespace Jxm\Ehr;


use GuzzleHttp\Client;
use Jxm\Ehr\Model\JxmEhrTokenInfos;
use Jxm\Tool\Tool;

class JxmEhrAccessHelper
{
    public static function validate(&$error, $infos, $rules, $msgs = null, $attributes = null): bool
    {
        $result = self::api($error, 'helper/checkData', null, [
            'rules' => $rules,
            'infos' => $infos,
            'msg' => $msgs,
            'attributes' => $attributes,
        ]);
        if ($result['code'] == 422) {
            $error = $result;
            return false;
        } else {
            return true;
        }
    }

    public static function api(&$error, $url, $tokenInfos, $params = [])
    {
        return self::postApi($error, config('ehr.api') . $url, $tokenInfos, $params);
    }

    /**
     * Notes:
     * User: harden - 2021/11/19 上午9:51
     * @param $error
     * @param $url
     * @param JxmEhrTokenInfos|null $tokenInfos
     * @param array $params
     * @param string $method
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function postApi(&$error, $url, $tokenInfos, $params = [], $method = 'POST')
    {
        try {
            $response = (new Client())->post($url, [
                'headers' => array_merge([
                    'X-Requested-With' => 'XMLHttpRequest',
                ], $tokenInfos ? [
                    'Authorization' => $tokenInfos->token_type . ' ' . $tokenInfos->access_token,
                ] : []),
                'form_params' => $params,
            ]);
            $result = json_decode($response->getBody()->getContents(), true);
            return $result;
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
            return null;
        }
    }

    public static function postRpc(string $module, string $class, string $method, array $args)
    {
        $client = new \Hprose\Http\Client(config('ehr.service') . $module . '/helper/' . $class, false);
        $client->byref = true;
        $result = $client->invoke($method, $args);
        return $result;
    }
}
