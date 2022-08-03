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
        'status' => 'normal'
    ],
    'userInfoWhereEnabled' => false,
    'userWhereQueryEnabled' => true,

    // Logged in user fields sent to brokers.
    'userFields' => [
        // Return array field name => database column name
        'id' => 'id',
        'username' => 'username',
        'email' => 'email',
        'mobile' => 'mobile',
        'image_url' => 'avatar',
        'nickname' => 'nickname',
        'status' => 'status',
        'uuid' => 'uuid',
        'cert_type' => 'cert_type',
        'has_blog' => 'has_blog',
        'blog_slug' => 'blog_slug'
    ],
    'before' => [
        'query' => function ($credentials) {
            
            return true;
        },
//        'multi' => function ($credentials) { 
            return true;
        // }
//        'common' => function ($credentials) {

            return true;
        // }
    ],

    'multi_enabled' => env('SSO_MULTI_ENABLED', false),

    'query_enabled' => env('SSO_QUERY_ENABLE', false),
    // 登录返回数据 支持bool(只返回true或false)/ array原样输出
    'loginReturnType' => 'bool',
    'redirectTo' => '/',
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
    // 是否重置session的过期时间
    'resetSessionTime' => env('SSO_RESET_SESSION_TIME', false),

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
    // broker cookie token expire time
    // default: 1 for one hour
    'token_expired_at' => env('SSO_BROKER_TOKEN_EXPIRED_AT', 1),
    /*
     |--------------------------------------------------------------------------
     | Settings necessary for the wechat if you need wechat-scan-sso
     |--------------------------------------------------------------------------
     |
     | These settings should be changed if this page is working as SSO broker.
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
