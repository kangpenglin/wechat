<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
<<<<<<< HEAD
        'autho/login',
        'activity',
        'me/{wechatId}',//明明设置了这个排除，但是就是无效？
        'activity/{wechatId}/created/{activityId}',
=======
        'autho/login', 'activity', 'activity/*/created/*',
>>>>>>> branchMain
        'activity/{wechatId}/detail/{activityId}',
        'activity/shift'
    ];
}
