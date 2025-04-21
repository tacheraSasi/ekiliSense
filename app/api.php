<?php
require_once __DIR__ . '/../vendor/autoload.php';

session_start();


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Api
{
    private static string $baseUrl = 'http://localhost:3000/api/v1';


    private static function getClient(): Client
    {
        return new Client([
            'base_uri' => self::$baseUrl,
            'headers'  => self::getHeaders(),
        ]);
    }

    private static function getHeaders(): array
    {
        $headers = ['Content-Type' => 'application/json'];
        if (!empty($_SESSION['token'])) {
            $headers['Authorization'] = 'Bearer ' . $_SESSION['token'];
        }
        return $headers;
    }

    private static function request(string $method, string $path, array $data = []): array
    {
        $client = self::getClient();
        try {
            $options = [];
            if ($method === 'GET') {
                $options['query'] = $data;
            } else {
                $options['json'] = $data;
            }

            $url = self::$baseUrl . $path;

            $response = $client->request($method, $url, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $message = $response ? $response->getBody()->getContents() : $e->getMessage();
            return ['status' => 'error', 'message' => $message];
        }
    }

    public static function login(string $email, string $password): array
    {
        $response = self::request('POST', '/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        if (!empty($response['token'])) {
            $_SESSION['token'] = $response['token'];
        }

        return $response;
    }

    public static function createSchool(array $data): array
    {
        return self::request('POST', '/schools', $data);
    }

    public static function updateSchool(int $id, array $data): array
    {
        return self::request('PUT', "/schools/{$id}", $data);
    }

    public static function getSchoolById(int $id): array
    {
        return self::request('GET', "/schools/{$id}");
    }
    
    public static function getSchoolByUniqueId(string $uniqueId): array
    {
        return self::request('GET', "/schools/unique/{$uniqueId}");
    }

    public static function getSchools(int $page = 1, int $limit = 10, string $search = ''): array
    {
        $query = [
            'page' => $page,
            'limit' => $limit,
        ];
        if (!empty($search)) {
            $query['search'] = $search;
        }
        return self::request('GET', '/schools', $query);
    }

    public static function deleteSchool(int $id): array
    {
        return self::request('DELETE', "/schools/{$id}");
    }

    public static function getTeacherById(int $id): array{
        return self::request("GET", "/teachers/{$id}");
    }

    public static function getTeachers(): array {
        return self::request("GET", "/teachers");
    }
    public static function getTeacherByEmail(int $email): array{
        return self::request("GET", "/teachers/{$email}");
    }
}
