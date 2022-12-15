<?php

namespace Jxm\Ehr\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jxm\Ehr\JxmApi;

class AppEsbMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $request->validate([
            'app_id' => 'required|string',
        ]);
        $request->setTrustedProxies($request->getClientIps(), Request::HEADER_X_FORWARDED_FOR);
        $ip = $request->getClientIp();
        $result = JxmApi::esb($error, 'helper/app_auth', [
            'app_id' => $request['app_id'],
            'ip' => $ip,
        ], null, null, true);
        if ($error) {
            abort(403, $error);
        }
        if ($result['res'] == true) {
            return $next($request);
        } else {
            abort(403, '当前APP未授权访问！');
        }
//        return $next($request);
    }
}
