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
            return;
        }
        $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'auth/logout',
            $this->ehr_token);
    }

    public function ehr_getInfo()
    {
        if (!$this->ehr_token || now()->gt($this->ehr_token->expires_at)) {
            return null;
        }
        $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'auth/info',
            $this->ehr_token);
        return $result;
    }
}
