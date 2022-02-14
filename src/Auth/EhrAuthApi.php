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
        $response = null;
        try {
            //            $response = $client->request('POST', config('ehr.oauth') . 'token', [
//                'form_params' => [
//                    'grant_type' => 'password',
//                    'client_id' => config('ehr.client.id'),
//                    'client_secret' => config('ehr.client.secret'),
//                    'username' => $username,
//                    'password' => $password,
//                    'scope' => '*',
//                ]]);
            $response = $client->request('POST', config('ehr.api') . 'auth/login', [
                'form_params' => [
                    'name' => $username,
                    'password' => $password,
                    'app_id' => $app_id ?: config('ehr.app_id'),
                ]]);
            $login_result = json_decode($response->getBody()->getContents(), true);

            $token = new JxmEhrTokenInfos([
                'token_type' => $login_result['data']['token_type'],
                'access_token' => $login_result['data']['access_token'],
                'expires_at' => Carbon::parse($login_result['data']['expires_at']),
            ]);
            return JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'auth/info',
                $token);
        } catch (\Exception $exception) {
            if ($response && $response->getStatusCode() != 400)
                Log::error('login fail:', $exception->getMessage());
            abort(403, $exception->getMessage());
//            abort(403, '登录失败，账号名或者密码错误！');
        }
    }

    public static function checkToken(JxmEhrTokenInfos $token)
    {
        $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'auth/info',
            $token);
        return $result;
    }
}
