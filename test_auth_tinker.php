<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Test Login
$request = Request::create('/api/login', 'POST', [
    'email' => 'admin@cinema.vn',
    'password' => 'password',
]);
$response = app()->handle($request);
$data = json_decode($response->getContent(), true);
echo "Login Status: " . $response->getStatusCode() . "\n";
$token = $data['access_token'] ?? null;

if ($token) {
    // Test Admin route
    $request2 = Request::create('/api/admin/test', 'GET');
    $request2->headers->set('Authorization', 'Bearer ' . $token);
    $response2 = app()->handle($request2);
    echo "Admin Route Status (Admin): " . $response2->getStatusCode() . "\n";
    echo "Admin Route Response: " . $response2->getContent() . "\n";
}

// Create a customer
$user = App\Models\User::factory()->create(['role' => 'customer', 'password' => bcrypt('password')]);
$tokenCustomer = $user->createToken('auth_token')->plainTextToken;

// Test Admin route with customer
$request3 = Request::create('/api/admin/test', 'GET');
$request3->headers->set('Authorization', 'Bearer ' . $tokenCustomer);
$response3 = app()->handle($request3);
echo "Admin Route Status (Customer): " . $response3->getStatusCode() . "\n";
echo "Admin Route Response: " . $response3->getContent() . "\n";

