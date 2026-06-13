<?php

namespace App\Controllers\Traits;

trait ApiResponseTrait
{
    private function successResponse(array|string $data, string $message = 'Success', int $code = 200): \CodeIgniter\HTTP\ResponseInterface
    {
        $payload = [
            'status'  => 'success',
            'message' => $message,
            'token'   => csrf_hash(),
        ];

        if (is_array($data) && array_key_exists('data', $data)) {
            $payload['data'] = $data['data'];
        } else {
            $payload['data'] = $data;
        }

        return $this->response->setJSON($payload)->setStatusCode($code);
    }

    private function errorResponse(string $message, int $code = 400, ?array $errors = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $payload = [
            'status'  => 'error',
            'message' => $message,
            'token'   => csrf_hash(),
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return $this->response->setJSON($payload)->setStatusCode($code);
    }

    private function paginatedResponse(array $items, int $total, int $page, int $perPage, string $message = 'Success'): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $message,
            'data'    => $items,
            'meta'    => [
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
                'last_page'=> (int) ceil($total / $perPage),
            ],
            'token' => csrf_hash(),
        ])->setStatusCode(200);
    }
}
