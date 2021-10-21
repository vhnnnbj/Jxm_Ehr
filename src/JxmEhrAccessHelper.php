<?php


namespace Jxm\Ehr;


use Jxm\Ehr\Model\JxmEhrTokenInfos;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class JxmEhrAccessHelper
{
    public static function postApi(&$error, $url, $token_type, $access_token, $params = [], $method = 'POST')
    {
        try {
            $response = (new Client())->post($url, [
                'headers' => array_merge([
                    'X-Requested-With' => 'XMLHttpRequest',
                ], ($token_type && $access_token) ? [
                    'Authorization' => $token_type . ' ' . $access_token,
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
