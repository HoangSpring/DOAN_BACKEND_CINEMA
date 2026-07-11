<?php
// separate process for customer
use Illuminate\Http\Request;

$user = App\Models\User::where('role', 'customer')->first();
$tokenCustomer = $user->createToken('auth_token')->plainTextToken;

$request3 = Request::create('/api/admin/test', 'GET');
$request3->headers->set('Authorization', 'Bearer ' . $tokenCustomer);
$response3 = app()->handle($request3);
echo "Admin Route Status (Customer): " . $response3->getStatusCode() . "\n";
echo "Admin Route Response: " . $response3->getContent() . "\n";
