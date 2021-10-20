<?php


namespace Jxm\Ehr;


use Jxm\Ehr\Model\JxmEhrTokenInfos;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class JxmEhrAccessHelper
{
    public static function postApi(&$error, $url, $params = [], $user_id = null, $method = 'POST')
    {
        if ($user_id) $token_info = JxmEhrTokenInfos::where('user_id', $user_id)->first();
        try {
            $response = (new Client())->post($url, [
                'headers' => array_merge([
                    'X-Requested-With' => 'XMLHttpRequest',
                ], $user_id ? [
                    'Authorization' => $token_info['token_type'] . ' ' . $token_info['access_token'],
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
