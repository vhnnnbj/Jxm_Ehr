<?php

namespace Jxm\Ehr\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

abstract class EhrAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = null;
        $token = $request->header('Authorization');
        $jwt = \trim((string)\preg_replace('/^\s*Bearer\s/', '', $token));
        $user = $this->getCache($token);
        if ($user) {
            app('auth')->guard()->setUser($user);
            return $next($request);
        }
        $track_id = random_int(1000000, 9999999);
        $params = [
            'api_track_msg_id' => $track_id,
        ];

        $client = new Client(['http_errors' => false]);
        $response = $client->request('POST', config('ehr.api') . 'auth/getId', [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ],
            'form_params' => $params,
        ]);
        if ($response->getStatusCode() != 200) {
            $response = $client->post(config('ehr.api') . 'helper/getErrorMessage', [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
                'form_params' => $params,
            ]);
            $result = json_decode($response->getBody()->getContents(), true);
            $result['code'] = $result['code'] ?? 500;
            $result['msg'] = $result['msg'] ?? '未知错误！';
            abort($result['code'], $result['msg']);
        } else {
            $result = json_decode($response->getBody()->getContents(), true);
            $ehr_id = $result['id'];
            $user = $this->getUser($ehr_id);
            if (!$user) {
                $response = $client->request('POST', config('ehr.api') . 'auth/info', [
                    'headers' => [
                        'X-Requested-With' => 'XMLHttpRequest',
                        'Authorization' => $token,
                    ],
                ]);
                $result = json_decode($response->getBody()->getContents(), true);
                DB::beginTransaction();
                $info = $result['data']['info'];
                $user = $this->newUser($ehr_id, $info);
                DB::commit();
            }
            app('auth')->guard()->setUser($user);
            $this->cacheUser($token, $ehr_id);
            $this->updateToken($user, $token);
        }
        return $next($request);
    }

    function getCache($authorization)
    {
        $ehr_id = Redis::get('Authorization:' . $authorization);
        if (!$ehr_id) return null;
        return $this->getUser($ehr_id);
    }

    function cacheUser($authorization, $ehr_id)
    {
        Redis::setex('Authorization:' . $authorization, 60, $ehr_id);
    }

    abstract function updateToken($user, $token);

    abstract function getUser($ehr_id);

    abstract function newUser($ehr_id, $infos);
}
