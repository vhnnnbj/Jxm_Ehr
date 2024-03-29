<?php


namespace Jxm\Ehr\Auth;


use App\Helpers\Tool;
use App\Helpers\UserRecordHelper;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Jxm\Ehr\JxmEhrAccessHelper;
use Jxm\Ehr\Model\JxmEhrTokenInfos;
use Modules\BaseFoundation\Entities\UserInfo;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class EhrAuthApi
{
    public static function login($username, $phone, $password, &$token, $app_id = null, $ip_addr = null)
    {

        $client = new Client();
        $response = null;
        $request = request();
        $request->setTrustedProxies($request->getClientIps(), Request::HEADER_X_FORWARDED_FOR);
        try {
            $ip_addr = $ip_addr ?: $request->getClientIp();
        } catch (\Exception $e) {
            $ip_addr = null;
        }
        $response = JxmEhrAccessHelper::api($error, 'auth/login',
            null, [
                'name' => $username,
                'phone' => $phone,
                'password' => $password,
                'app_id' => $app_id ?: config('ehr.app_id'),
                'ip_address' => $ip_addr,
            ], false);
        $login_result = $response;

        $token = new JxmEhrTokenInfos([
            'token_type' => $login_result['data']['token_type'],
            'access_token' => $login_result['data']['access_token'],
            'refresh_token' => '',
            'expires_at' => Carbon::parse($login_result['data']['expires_at']),
        ]);
        return JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'auth/info',
            $token);
    }

    public static function checkToken(JxmEhrTokenInfos $token)
    {
        $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'auth/info',
            $token);
        return $result;
    }
}
