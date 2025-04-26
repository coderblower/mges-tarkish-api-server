<?php
return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'candidate_photos/*', 'candidate_qrcode/*'],  // Allow these paths

    'allowed_methods' => ['*'],  // Allow all HTTP methods

    'allowed_origins' => ['http://localhost:5173', 'https://mges.global'],  // Allow only your front-end origin

    'allowed_headers' => ['*'],  // Allow all headers

    'supports_credentials' => true,  // If you need to send credentials (cookies, etc.)

];
