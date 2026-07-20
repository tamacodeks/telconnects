<?php

namespace App\Support;

use GuzzleHttp\Client;

class BusV2ApiClient
{
    private $client;
    private $token;

    public function __construct(Client $client, $token)
    {
        $this->client = $client;
        $this->token = $token;
    }

    public function cities()
    {
        return $this->client->request('GET', 'bus/v2', [
            'headers' => $this->headers(),
        ]);
    }

    public function search(array $params, $roundTrip = false)
    {
        return $this->client->request('GET', $roundTrip ? 'bus/v2/roundtrip/search' : 'bus/v2/search', [
            'headers' => $this->headers(),
            'query' => $params,
        ]);
    }

    public function createFlixReservation(array $params, $roundTrip = false)
    {
        return $this->client->request('POST', $roundTrip ? 'bus/v2/flix/roundtrip/reservations' : 'bus/v2/flix/reservations', [
            'headers' => $this->headers(),
            'form_params' => $params,
        ]);
    }

    public function createBlaReservation(array $payload, $roundTrip = false)
    {
        return $this->client->post($roundTrip ? 'bus/v2/bla/roundtrip/reservations' : 'bus/v2/bla/reservations', [
            'headers' => $this->headers(true),
            'json' => $payload,
        ]);
    }

    public function confirmFlix(array $params)
    {
        return $this->client->request('POST', 'bus/v2/flix/passengers', [
            'headers' => $this->headers(),
            'form_params' => $params,
        ]);
    }

    public function confirmBla(array $payload)
    {
        return $this->client->request('POST', 'bus/v2/bla/passengers', [
            'headers' => $this->headers(true),
            'json' => $payload,
        ]);
    }

    public function tripStops($tripPayload)
    {
        return $this->client->request('POST', 'bus/v2/trip-stops', [
            'headers' => $this->headers(),
            'form_params' => [
                'trip' => (string) $tripPayload,
            ],
        ]);
    }

    private function headers($json = false)
    {
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token,
        ];

        if ($json) {
            $headers['Content-Type'] = 'application/json';
        }

        return $headers;
    }
}
