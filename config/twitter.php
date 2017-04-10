<?php

return [
    'CONSUMER_KEY'        => function_exists('env') ? env('TWITTER_CONSUMER_KEY', '') : '',
    'CONSUMER_SECRET'     => function_exists('env') ? env('TWITTER_CONSUMER_SECRET', '') : '',
    'ACCESS_TOKEN'        => function_exists('env') ? env('TWITTER_ACCESS_TOKEN', '') : '',
    'ACCESS_TOKEN_SECRET' => function_exists('env') ? env('TWITTER_ACCESS_TOKEN_SECRET', '') : '',
];
