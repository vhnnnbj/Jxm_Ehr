<?php


namespace Jxm\Ehr;


use Illuminate\Database\Eloquent\Relations\MorphOne;
use Jxm\Ehr\Model\JxmEhrTokenInfos;

trait HasEhrTokenInfo
{
    public function ehr_token(): MorphOne
    {
        return $this->morphOne(JxmEhrTokenInfos::class, 'user');
    }

    public function ehr_logout()
    {
        if (!$this->ehr_token) {
            $token = explode(' ', request()->header('Authorization'))[1] ?? '';
        }
        $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'auth/logout',
            $this->ehr_token ?: $token);
    }

    public function ehr_getInfo()
    {
        if (!$this->ehr_token || now()->gt($this->ehr_token->expires_at)) {
            $token = explode(' ', request()->header('Authorization'))[1] ?? '';
        }
        $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'auth/info',
            $this->ehr_token?:$token);
        if (!$result) {
            abort(403, $error);
        }
        return $result;
    }
}
