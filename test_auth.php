<?php

use Illuminate\Support\Facades\Http;

$response = Http::post('http://127.0.0.1:8000/api/login', [
    'email' => 'admin@cinema.vn',
    'password' => 'password',
]);

$data = $response->json();
echo "Login Status: " . $response->status() . "\n";
$token = $data['access_token'] ?? null;

if ($token) {
    $adminResponse = Http::withToken($token)->get('http://127.0.0.1:8000/api/admin/test');
    echo "Admin Route Status (Admin User): " . $adminResponse->status() . "\n";
    echo "Admin Route Response: " . $adminResponse->body() . "\n";
}

$responseCustomer = Http::post('http://127.0.0.1:8000/api/login', [
    'email' => 'customer@cinema.vn', // Actually we used factory for customer, wait! We need to create a test customer first.
    'password' => 'password',
]);
