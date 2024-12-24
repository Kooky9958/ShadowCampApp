<?php

namespace App\Helpers;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TerraApi
{

    private $client;

    public function __construct()
    {
        $this->client = Http::baseUrl(config('terra.endpoint'))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'dev-id'    => config('terra.dev_id'),
                'x-api-key' => config('terra.api_key')
            ]);
    }

    function get(string $url, array $queryParams = [])
    {
        $data = [
            'success' => false,
            'message' => null,
            'code'    => null,
            'data'    => null,
        ];

        try {
            $response = $this->client->throw()->get($url, $queryParams);

            $data['success'] = true;
            $data['data'] = $response->json();
            $data['code'] = $response->status();
        } catch (RequestException $exception) {

            $data['message'] = $exception->getMessage();
            $data['data'] = $exception->response->json();
            $data['code'] = $exception->response->status();
        } catch (\Throwable $th) {
            Log::error('TERRA_API_GET: ' . $th->getMessage());

            $data['message'] = 'Unknown error';
        }

        return $data;
    }

    function post(string $url, array $payload = [])
    {
        $data = [
            'success' => false,
            'message' => null,
            'data'    => null,
        ];

        try {
            $response = $this->client->throw()->post($url, $payload);

            $data['success'] = true;
            $data['data'] = $response->json();
            $data['code'] = $response->status();
        } catch (RequestException $exception) {

            $data['message'] = $exception->getMessage();
            $data['data']    = $exception->response->json();
            $data['code']    = $exception->response->status();
        } catch (\Throwable $th) {
            Log::error('TERRA_API_GET: ' . $th->getMessage());

            $data['message'] = 'Unknown error';
        }

        return $data;
    }

    function delete(string $url, array $query = [])
    {
        $data = [
            'success' => false,
            'message' => null,
            'data'    => null,
        ];

        try {
            $response = $this->client->throw()->withQueryParameters($query)->delete($url);

            $data['success'] = true;
            $data['data'] = $response->json();
            $data['code'] = $response->status();
        } catch (RequestException $exception) {

            $data['message'] = $exception->getMessage();
            $data['data']    = $exception->response->json();
            $data['code']    = $exception->response->status();
        } catch (\Throwable $th) {
            Log::error('TERRA_API_GET: ' . $th->getMessage());

            $data['message'] = 'Unknown error';
        }

        return $data;
    }
}
