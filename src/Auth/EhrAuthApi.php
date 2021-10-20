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
use Modules\BaseFoundation\Entities\UserInfo;

class EhrAuthApi
{
    public function login($username, $password)
    {

        $client = new Client();
        try {
            $response = $client->request('POST', config('ehr.oauth') . 'token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => config('ehr.client.id'),
                    'client_secret' => config('ehr.client.key'),
                    'username' => $username,
                    'password' => $password,
                    'scope' => '*',
                ]]);

            $login_result = json_decode($response->getBody()->getContents(), true);

            $response = (new Client())->get(config('ehr.api') . 'user', [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Authorization' => $login_result['token_type'] . ' ' . $login_result['access_token'],
                ],
            ]);
            $result = json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $exception) {
            abort(401, '登录失败，账号名或者密码错误！');
        }

        $user = User::whereHasIn('userinfo', function ($q) use ($result) {
            $q->where('sc_id', $result['user']['userinfo']['id']);
        })->first();
        if (!$user) {
            DB::beginTransaction();
            try {
                $userinfo = new UserInfo(Arr::only($result['user']['userinfo'], [
                    'username', 'sex', 'identityNumber', 'address', 'phone', 'email', 'details', 'describe', 'entrytime', 'leavetime',
                ]));
                $userinfo->editor_id = 1;
                $userinfo->save();
                $userinfo->editor_id = $userinfo->id;
                $userinfo->save();
                $user = new User(Arr::only($result['user'], [
                    'name'
                ]));
                $user->userinfo_id = $userinfo->id;
                $user->password = Hash::make('thisisafakepassword!');
                $user->save();

                DB::commit();
            } catch (\Exception $exception) {
                Log::error('save user', $result);
                abort('403', '创建用户失败！');
            }
        } else {
            $user->userinfo->update(Arr::only($result['user']['userinfo'], [
                'username', 'sex', 'identityNumber', 'address', 'phone', 'email', 'details', 'describe', 'entrytime', 'leavetime',
            ]));
            $user->userinfo->save();
            $user->update('name', Arr::only($result['user'], ['name']));
            $user->save();
        }
        $user->userinfo->setToken($login_result['token_type'] . ' ' . $login_result['access_token'], $login_result['expires_in']);

        Auth::login($user);
        $user = Auth::user();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        //if ($request->remember_me)
        $token->expires_at = Carbon::now()->addDays(1);

        $token->save();

        $result = array(
            'access_token' => $tokenResult->accessToken,
            'sc_token' => $login_result['access_token'],
            'token_type' => 'Bearer',
            'user_id' => $user->id,
            'is_user' => $user->userinfo ? true : false,
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at),
        );

        if ($user->userinfo) {
            if ($user->userinfo->state != 0) {
                return response()->json([
                    'message' => '人事档案已' . ($user->userinfo->state == 1 ? '离职' : '冻结') . '，不能登录！',
                ], 403);
            }
            UserRecordHelper::makeRecord($user->userinfo_id, UserInfo::Record_Login, '登录',
                json_encode([
                    'ip' => $request->getClientIp(),
                ]), Carbon::now() . ' 登录,ip地址为：' . $request->getClientIp());
        }

        return Tool::formatOutput($request, $result, null, ErrorCode::ERR_OK, '登录成功');
//        return Tool::commonDataInput(ErrorCode::ERR_OK, $result, 3, '登录成功');
    }

    public function logout()
    {

    }
}