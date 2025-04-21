<?php

class Api
{
    private static string $baseUrl = 'http:localhost:3000/api/v1';
    
    private static function getHeaders(): array
    {
        $headers = [
            'Content-Type: application/json',
        ];
        if (isset($_SESSION['token'])) {
            $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
        }
        return $headers;
    }

    private static function request(string $method, string $path, array $data = []): array
    {
        $url = self::$baseUrl . $path;
        $options = [
            'http' => [
                'method'  => $method,
                'header'  => implode("\r\n", self::getHeaders()),
                'ignore_errors' => true,
            ]
        ];

        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        } elseif ($method !== 'GET') {
            $options['http']['content'] = json_encode($data);
        }

        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        return json_decode($response ?: '{}', true);
    }

    public static function login(string $email, string $password): array
    {
        $response = self::request('POST', '/auth/login', [
            'email' => $email,
            'password' => $password
        ]);

        if (isset($response['access_token'])) {
            $_SESSION['token'] = $response['token'];
            $_SESSION['user'] = $response['user'];
        }

        return $response;
    }

    public static function createSchool(array $schoolData): array
    {
        return self::request('POST', '/schools', $schoolData);
    }

    public static function updateSchool(string $schoolId, array $data): array
    {
        return self::request('PATCH', "/schools/{$schoolId}", $data);
    }

    public static function getSchools(array $query = []): array
    {
        return self::request('GET', '/schools', $query);
    }

    public static function getSchoolById(string $id): array
    {
        return self::request('GET', "/schools/{$id}");
    }
}
