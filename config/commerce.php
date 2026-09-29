<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Delivery Charges
    |--------------------------------------------------------------------------
    |
    | Orders whose total (after item discounts) reaches "free_above" ship free;
    | smaller orders pay the flat "fee". Amounts are in rupees.
    |
    */

    'delivery' => [
        'free_above' => (float) env('DELIVERY_FREE_ABOVE', 499),
        'fee' => (float) env('DELIVERY_FEE', 99),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap
    |--------------------------------------------------------------------------
    |
    | Where "sitemap:generate" and the product observer write the sitemap.
    | It must stay inside public/ in production so /sitemap.xml is served.
    |
    */

    'sitemap' => [
        'path' => public_path('sitemap.xml'),
    ],

];
