<?php

namespace App\Controllers;

use App\Models\TripModel;
use CodeIgniter\HTTP\ResponseInterface;

class TripApi extends BaseController
{
    protected TripModel $tripModel;

    public function __construct()
    {
        $this->tripModel = new TripModel();
    }

    public function destinationSuggest(): ResponseInterface
    {
        $q = trim((string) $this->request->getGet('q'));

        if ($q === '' || mb_strlen($q) < 2) {
            return $this->response->setJSON([
                'results' => [],
            ]);
        }

        try {
            $client = service('curlrequest');

            $response = $client->get('https://geocoding-api.open-meteo.com/v1/search', [
                'query' => [
                    'name'     => $q,
                    'count'    => 8,
                    'language' => 'en',
                    'format'   => 'json',
                ],
                'http_errors' => false,
                'timeout'     => 10,
                'verify'      => false,
            ]);

            $data = json_decode($response->getBody(), true);

            return $this->response->setJSON([
                'results' => $data['results'] ?? [],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'results' => [],
                'error'   => 'Unable to fetch destination suggestions.',
            ]);
        }
    }

    public function weather(): ResponseInterface
    {
        $latRaw = $this->request->getGet('latitude');
        $lngRaw = $this->request->getGet('longitude');

        if ($latRaw === null || $lngRaw === null || $latRaw === '' || $lngRaw === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'Latitude and longitude are required.',
            ]);
        }

        $latitude = (float) $latRaw;
        $longitude = (float) $lngRaw;

        try {
            $client = service('curlrequest');

            $response = $client->get('https://api.open-meteo.com/v1/forecast', [
                'query' => [
                    'latitude'  => $latitude,
                    'longitude' => $longitude,
                    'current'   => 'temperature_2m,apparent_temperature,weather_code,wind_speed_10m',
                    'timezone'  => 'auto',
                ],
                'verify' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $data = json_decode($body, true);

            if ($statusCode !== 200) {
                return $this->response->setStatusCode($statusCode)->setJSON([
                    'error' => 'Weather API returned an error.',
                    'status_code' => $statusCode,
                    'raw_body' => $body,
                ]);
            }

            if (!is_array($data) || !isset($data['current'])) {
                return $this->response->setStatusCode(500)->setJSON([
                    'error' => 'Invalid weather response received.',
                    'raw_body' => $body,
                ]);
            }

            return $this->response->setJSON($data);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Unable to fetch weather data.',
                'details' => $e->getMessage(),
            ]);
        }
    }

    public function liveSearch(): ResponseInterface
    {
        $q = trim((string) $this->request->getGet('q'));

        $builder = $this->tripModel->orderBy('id', 'DESC');

        if ($q !== '') {
            $builder = $builder
                ->groupStart()
                ->like('destination', $q)
                ->orLike('country', $q)
                ->orLike('notes', $q)
                ->groupEnd();
        }

        $trips = $builder->findAll();

        return $this->response->setJSON([
            'trips' => $trips,
        ]);
    }

    public function nearbyTrips(): ResponseInterface
    {
        $latitude  = (float) $this->request->getGet('latitude');
        $longitude = (float) $this->request->getGet('longitude');
        $radius    = (float) ($this->request->getGet('radius') ?: 50);

        if (!$latitude || !$longitude) {
            return $this->response->setStatusCode(422)->setJSON([
                'trips' => [],
                'error' => 'Latitude and longitude are required.',
            ]);
        }

        $db = \Config\Database::connect();

        $sql = "
            SELECT
                id,
                destination,
                country,
                travel_date,
                budget,
                notes,
                latitude,
                longitude,
                created_at,
                updated_at,
                (
                    6371 * ACOS(
                        COS(RADIANS(?)) *
                        COS(RADIANS(latitude)) *
                        COS(RADIANS(longitude) - RADIANS(?)) +
                        SIN(RADIANS(?)) *
                        SIN(RADIANS(latitude))
                    )
                ) AS distance_km
            FROM trips
            WHERE latitude IS NOT NULL
              AND longitude IS NOT NULL
            HAVING distance_km <= ?
            ORDER BY distance_km ASC
        ";

        $query = $db->query($sql, [$latitude, $longitude, $latitude, $radius]);

        return $this->response->setJSON([
            'trips' => $query->getResultArray(),
        ]);
    }
}