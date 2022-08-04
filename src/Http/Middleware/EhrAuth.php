<?php

namespace Jxm\Ehr\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jxm\Ehr\JxmEsb;
use Jxm\Ehr\Model\JxmEhrTokenInfos;

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
            $result = JxmEsb::errMsg($params['api_track_msg_id']);
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
            $this->updateToken($user, $token);
        }
        return $next($request);
    }

    abstract function updateToken($user, $token);

    abstract function getUser($ehr_id);

    abstract function newUser($ehr_id, $infos);
}
