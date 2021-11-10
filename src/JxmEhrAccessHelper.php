<?php


namespace Jxm\Ehr;


use GuzzleHttp\Client;
use Jxm\Ehr\Model\JxmEhrTokenInfos;
use Jxm\Tool\Tool;

class JxmEhrAccessHelper
{
    public static function postApi(&$error, $url, JxmEhrTokenInfos $tokenInfos, $params = [], $method = 'POST')
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
