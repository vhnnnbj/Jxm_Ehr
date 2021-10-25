<?php


namespace Jxm\Ehr\Auth;


use App\Helpers\Tool;
use App\Helpers\UserRecordHelper;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Jxm\Ehr\JxmEhrAccessHelper;
use Jxm\Ehr\Model\JxmEhrTokenInfos;
use Modules\BaseFoundation\Entities\UserInfo;

class EhrAuthApi
{
    public static function login($username, $password, &$token)
    {

        $client = new Client();
        try {
            $response = $client->request('POST', config('ehr.oauth') . 'token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => config('ehr.client.id'),
                    'client_secret' => config('ehr.client.secret'),
                    'username' => $username,
                    'password' => $password,
                    'scope' => '*',
                ]]);
            $login_result = json_decode($response->getBody()->getContents(), true);

            $token = new JxmEhrTokenInfos([
                'token_type' => $login_result['token_type'],
                'access_token' => $login_result['access_token'],
                'refresh_token' => $login_result['refresh_token'],
                'expires_at' => now()->addSeconds($login_result['expires_in']),
            ]);
            $response = (new Client())->post(config('ehr.api') . 'auth/info', [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Authorization' => $login_result['token_type'] . ' ' . $login_result['access_token'],
                ],
            ]);
            $result = json_decode($response->getBody()->getContents(), true);
            return $result;
        } catch (\Exception $exception) {
            abort(401, '登录失败，账号名或者密码错误！');
        }
    }
}
