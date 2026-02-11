<?php

namespace RSA\Spacebring\Api;

use RSA\Spacebring\Helpers\Credentials;
use RuntimeException;

class Client
{
    private const API_BASE_URL = 'https://api.spacebring.com';

    /**
     * Make an API request
     *
     * @param string $endpoint   e.g. '/locations/v1'
     * @param string $method     GET|POST|PUT|PATCH|DELETE
     * @param array  $query      Query parameters
     * @param array  $body       JSON body
     * @param array  $headers    Extra headers
     *
     * @return array
     */
    public function request(
        string $endpoint,
        string $method = 'GET',
        array $query = [],
        array $body = [],
        array $headers = []
    ): array {
        if (!Credentials::exists()) {
            throw new RuntimeException('Spacebring API credentials are missing.');
        }

        $url = $this->buildUrl($endpoint, $query);

        $args = [
            'method' => strtoupper($method),
            'timeout' => 20,
            'headers' => array_merge([
                'Authorization' => $this->authorizationHeader(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ], $headers),
        ];

        if (!empty($body) && in_array($args['method'], ['POST', 'PUT', 'PATCH'], true)) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            throw new RuntimeException($response->get_error_message());
        }

        $status = wp_remote_retrieve_response_code($response);
        $raw = wp_remote_retrieve_body($response);
        $data = json_decode($raw, true);

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException(sprintf(
                'Spacebring API error (%d): %s',
                $status,
                $raw
            ));
        }

        return $data ?? [];
    }

    /**
     * Build full API URL
     */
    private function buildUrl(string $endpoint, array $query = []): string
    {
        $endpoint = '/' . ltrim($endpoint, '/');

        $url = self::API_BASE_URL . $endpoint;

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    /**
     * Build Authorization header
     */
    private function authorizationHeader(): string
    {
        $username = Credentials::username();
        $password = Credentials::password();

        return 'Basic ' . base64_encode("{$username}:{$password}");
    }
}