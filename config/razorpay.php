<?php

return [
    'key' => env('RAZORPAY_KEY', env('RAZORPAY_KEY_ID', '')),
    'secret' => env('RAZORPAY_SECRET', env('RAZORPAY_KEY_SECRET', '')),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
];
