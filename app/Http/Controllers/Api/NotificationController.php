<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\firebaseService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(firebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function sendPushNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $user = auth()->user();
        $token = $user->device_token; // Ensure the device token is stored in the user model

        if ($token) {
            $title = $request->input('title');
            $body = $request->input('body');
            $data = $request->input('data', []);

            $this->firebaseService->sendNotification([$token], $title, $body, $data);

            return response()->json(['message' => 'Notification sent successfully'], 200);
        }

        return response()->json(['message' => 'No device token found'], 400);
    }

}