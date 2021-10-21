<?php


namespace Jxm\Ehr;


use Illuminate\Database\Eloquent\Relations\MorphOne;
use Jxm\Ehr\Model\JxmEhrTokenInfos;

trait HasEhrInfo
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
            $this->ehr_token->token_type, $this->ehr_token->access_token);
    }

    public function ehr_getInfo()
    {
        if (!$this->ehr_token) {
            return null;
        }
        $result = JxmEhrAccessHelper::postApi($error, config('ehr.api') . 'auth/info',
            $this->ehr_token->token_type, $this->ehr_token->access_token);
        return $result;
    }
}
