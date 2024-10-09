<?php
// session_start();
// require '../config.php';  
// require '../parseEnv.php';  already requiresd in config.php
require '../../vendor/autoload.php';
function syncPlanWithGoogleCalendar($conn, $email, $title, $desc) {
    // Fetching teacher details from the database
    $query = "SELECT * FROM teachers_google WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $teacher = mysqli_fetch_assoc($result);

        // Using the teacher's OAuth tokens to create the Google Client
        $client = new Google_Client();
        $client->setApplicationName('ekiliSense');
        $client->setClientId(getenv('GOOGLE_CLIENT_ID')); // Fetching from environment variables
        $client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
        $client->addScope(Google_Service_Calendar::CALENDAR);

        // Setting the teacher's stored tokens
        $token = [
            'access_token' => $teacher['access_token'],
            'refresh_token' => $teacher['refresh_token'],
            'expires_in' => $teacher['expires_in'],
            'created' => strtotime($teacher['token_created_at'])
        ];
        $client->setAccessToken($token);

        // Refreshing the token if it's expired
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

            // Saving the updated tokens
            saveGoogleTokens($teacher['id'], $client->getAccessToken(), $conn);
        }

        // Creating the event
        $service = new Google_Service_Calendar($client);

        // Setting start and end times in Dar es Salaam timezone
        $startDateTime = date('Y-m-d\TH:i:s', strtotime('now'));
        $endDateTime = date('Y-m-d\TH:i:s', strtotime('+1 month')); // End time one month from now
        $timeZone = 'Africa/Dar_es_Salaam';  // Set to Dar es Salaam timezone

        // Defining the event details
        $event = new Google_Service_Calendar_Event(array(
            'summary' => $title,
            'description' => $desc,
            'start' => array(
                'dateTime' => $startDateTime,
                'timeZone' => $timeZone,  // Set timezone
            ),
            'end' => array(
                'dateTime' => $endDateTime,
                'timeZone' => $timeZone,  // Set timezone
            ),
        ));

        // Inserting the event into the teacher's primary calendar
        $calendarId = 'primary';
        $event = $service->events->insert($calendarId, $event);

        return $event->htmlLink; // Returning event link for reference
    } else {
        echo "Teacher details not found!";
        return;
    }
}


function saveGoogleTokens($teacher_id, $token, $conn) {
    $access_token = mysqli_real_escape_string($conn, $token['access_token']);
    $refresh_token = mysqli_real_escape_string($conn, $token['refresh_token']);
    $expires_in = $token['expires_in'];
    $created_at = date('Y-m-d H:i:s', $token['created']);

    // Inserting or update tokens in the database
    $query = "UPDATE teachers_google 
              SET access_token = '$access_token', 
                  refresh_token = '$refresh_token',
                  expires_in = $expires_in,
                  token_created_at = '$created_at'
              WHERE id = $teacher_id";
              
    mysqli_query($conn, $query);
}
