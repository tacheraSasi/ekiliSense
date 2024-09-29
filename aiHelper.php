<?php
// Allow CORS for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
header('Content-Type: application/json');

require 'vendor/autoload.php';
require 'parseEnv.php';

parseEnv(__DIR__ . '/.env');

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Capture the user's input from a JavaScript request
$userInput = file_get_contents('php://input');
$userInput = json_decode($userInput, true)['prompt'] ?? "Default prompt here.";

// Start a session to maintain conversation history
session_start();

if (!isset($_SESSION['conversation'])) {
    $_SESSION['conversation'] = [];
}

$_SESSION['conversation'][] = ['role' => 'user', 'content' => $userInput];

/**
 * Function to communicate with OpenAI's API, representing AI therapist "Magreth."
 * Magreth specializes in providing compassionate, professional advice to survivors of sexual assault and rape.
 * 
 * @param array $conversation
 * @return array
 */
function openAi($conversation) {
    $client = new Client();

    try {
        // Send a POST request to OpenAI's API
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . getenv('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'messages' => array_merge(
                    [
                        [
                            'role' => 'system', 
                            'content' => 'You are an AI assistant for ekiliSense an AI automated school management system, reply in short messages in a chat like way' 
                        ]
                    ], 
                    $conversation 
                ),
                'model' => 'gpt-3.5-turbo',
                'temperature' => 0.65,
                'top_p' => 1,
                'frequency_penalty' => 0.2,
                'presence_penalty' => 0.6,
            ],
        ]);

        // Decode the response
        $body = $response->getBody();
        $data = json_decode($body, true);

        // Capture and store the assistant's response in the conversation
        $assistantResponse = $data['choices'][0]['message']['content'];
        $_SESSION['conversation'][] = ['role' => 'assistant', 'content' => $assistantResponse];

        return ['status' => 'success', 'text' => $assistantResponse];

    } catch (RequestException $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

// Call the openAi function with the conversation history and return the assistant's response as JSON
header('Content-Type: application/json');
echo json_encode(openAi($_SESSION['conversation']));