<?php
use Illuminate\Support\Facades\Cache;
return [
    /*
     |--------------------------------------------------------------------------
     | Laravel SSO Settings
     |--------------------------------------------------------------------------
     |
     | Set type of this web page. Possible options are: 'server' and 'broker'.
     |
     | You must specify either 'server' or 'broker'.
     |
     */

    'type' => 'server',

    /*
     |--------------------------------------------------------------------------
     | Settings necessary for the SSO server.
     |--------------------------------------------------------------------------
     |
     | These settings should be changed if this page is working as SSO server.
     |
     */

    'usersModel' => \App\User::class,
    'brokersModel' => Lysice\LaravelSSO\Models\Broker::class,

    // Table used in Lysice\LaravelSSO\Models\Broker model
    'brokersTable' => 'brokers',

    'userWhere' => [
        // auth condition like this.it will be used for user select sql.
        // for example before the sql is select * from user where username = xxx and password = xx
        // now it will be select * from user where username = xxx and password = xx and status = normal
        // 'status' => 'normal'
    ],
    // whether enabled where condition when get userInfo
    'userInfoWhereEnabled' => false,
    // Logged in user fields sent to brokers.
    'userFields' => [
        // Return array field name => database column name
        'id' => 'id',
    ],

    'multi_enabled' => env('SSO_MULTI_ENABLED', false),
    'redirectTo' => '/',
    //        'attach' => [
    //            'GET','POST'
    //        ],
    //        'logout' => [
    //            'POST'
    //        ]
    'supports' => [
        'attach' => [
            'GET','POST'
        ],
        'logout' => [
            'POST'
        ]
    ],
    // server session expire time
    // default: 1 for one hour
    'expired_time' => env('SSO_BROKER_SESSION_EXPIRED_TIME', 1),

    /*
     |--------------------------------------------------------------------------
     | Settings necessary for the SSO broker.
     |--------------------------------------------------------------------------
     |
     | These settings should be changed if this page is working as SSO broker.
     |
     */

    'serverUrl' => env('SSO_SERVER_URL', null),
    'brokerName' => env('SSO_BROKER_NAME', null),
    'brokerSecret' => env('SSO_BROKER_SECRET', null),

    /*
     |--------------------------------------------------------------------------
     | Settings necessary for the wechat if you need wechat-scan-sso
     |--------------------------------------------------------------------------
     |
     | These settings should be changed if you use api check support
     |
     */
    'api' => [
        'enabled' => env('SSO_API_ENABLED', false),
        // wechat result merged extra data callback
        'getMerged' => ['uses' => '\App\Services\SSOService@getMerged'],
        // wechat userId
        'getUserId' => ['uses' => '\App\Services\SSOService@getUserId'],
        'getPassword' => ['uses' => '\App\Services\SSOService@getPassword']
    ]
];
