<?php

return [
    'public_key' => env('PAYSTACK_PUBLIC_KEY', 'pk_test_9e77b7a4f398a6eea1e088064e759a6f97eabf11'),
    'secret_key' => env('PAYSTACK_SECRET_KEY', 'sk_test_518d784a7094dc2c72a55bd26d0c04447a8a7df2'),
    'merchant_email' => env('PAYSTACK_MERCHANT_EMAIL'),
    'callback_url' => env('PAYSTACK_CALLBACK_URL'),
]; 