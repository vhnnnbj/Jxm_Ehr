<?php

namespace Jxm\Ehr\Http\Middleware;

use App\Models\User;
use Closure;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $params = [];

        $client = new Client(['http_errors' => false]);
        $response = $client->request('POST', config('ehr.api') . 'auth/getId', [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => $token,
            ],
        ]);
        if ($response->getStatusCode() != 200) {
            $result['code'] = $response->getStatusCode();
            $result['message'] = $response->getReasonPhrase();
            abort($result['code'], $result['message']);
        } else {
            $result = json_decode($response->getBody()->getContents(), true);
            $user = $this->getUser($request['id']);
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
                $user = $this->newUser($request['id'], $info);
                DB::commit();
            }
            app('auth')->guard()->setUser($user);
        }
        return $next($request);
    }

    abstract function getUser($ehr_id);

    abstract function newUser($ehr_id, $infos);
}
