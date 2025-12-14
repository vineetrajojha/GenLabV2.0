<?php
namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Log; 

class FCMService
{
    public function sendNotification($token, $title, $body, $data = [])
    {
        // Load Firebase service account file
        $client = new Client();
        $client->setAuthConfig(storage_path('app/' . env('FIREBASE_CREDENTIALS')));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

        $data = array_map(fn($v) => (string) $v, $data); 
        // Build message
        $message = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],
                "data" => $data
            ]
        ];

        // Send to FCM v1 endpoint
        $url = "https://fcm.googleapis.com/v1/projects/" . env('FIREBASE_PROJECT_ID') . "/messages:send";

         
        $response = \Http::withToken($accessToken)
            ->post($url, $message);
        
        Log::info("FCMService response", [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);  

        return $response->json();
    }
}
