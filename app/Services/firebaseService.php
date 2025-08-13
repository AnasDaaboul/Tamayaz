<?php
namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class firebaseService
{
    protected $messaging;
    public function __construct()
    {
        $serviceAccountPath = ("/Users/anas/Desktop/CoursesProject/public_html/courses-project-9cc18-firebase-adminsdk-xil46-a7ff261dbe.json");
        $factory = (new Factory)->withServiceAccount($serviceAccountPath);
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($tokens, $title, $body, $data = [])
    {
        $message = CloudMessage::new()
            ->withNotification(['title' => $title, 'body' => $body])
            ->withData($data);

        if (is_array($tokens)) {
            foreach ($tokens as $token) {
                $this->messaging->send($message->withChangedTarget('token', $token));
            }
        } else {
            $this->messaging->send($message->withChangedTarget('topic', $tokens));
        }
    }
}