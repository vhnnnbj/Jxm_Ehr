<?php

namespace Jxm\Ehr;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Jxm\Ehr\Model\BgDepartment;
use Jxm\Ehr\Model\UserInfo;

class EhrServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/ehr.php' => config_path('ehr.php'),
        ], 'ehr_config');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
//        $this->loadRoutesFrom(__DIR__ . '../route/route.php');

        Validator::extend('iam_user_exists', function ($attribute, $value,
                                                       $parameters, $validator) {
            if (sizeof($parameters) == 0) {
                $query = UserInfo::where('id', $value);
            } else {
                $query = UserInfo::where($parameters[0], $value);
            }
            return $query->exists();
        }, '未查询到指定用户信息。');
        Validator::extend('iam_department_exists', function ($attribute, $value,
                                                             $parameters, $validator) {
            $query = BgDepartment::where('id', $value);
            return $query->exists();
        }, '未查询到指定部门信息。');
    }
}
