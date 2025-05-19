<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'auth/*', 'sadmin/*'], // Include sadmin routes
    'allowed_methods' => ['*'], // Allow all HTTP methods (GET, POST, OPTIONS, etc.)
    'allowed_origins' => ['http://localhost:4200'], // Explicitly allow Angular origin
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'], // Allow all headers (e.g., Authorization, Content-Type)
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false, // Set to false for token-based auth (Sanctum)
];
