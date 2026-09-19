<?php

return [
    'merchant_id' => env('PG_MERCHANT_ID', '100000000007164'),
    'aggregator_id' => env('PG_AGGREGATOR_ID', 'A100000000007164'),
    'secret_key' => env('PG_SECRET_KEY', 'db06cca0-838b-4e01-8b20-6ac446ffb6bd'),
    'initiate_sale_url' => env('PG_INITIATE_URL', 'https://pgpayuat.icicibank.com/tsp/pg/api/v2/initiateSale'),
    'status_check_url' => env('PG_STATUS_URL', 'https://pgpayuat.icicibank.com/tsp/pg/api/command'),
];