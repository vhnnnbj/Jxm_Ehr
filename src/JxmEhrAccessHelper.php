<?php


namespace Jxm\Ehr;


use GuzzleHttp\Client;
use Jxm\Ehr\Model\JxmEhrTokenInfos;

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
}
