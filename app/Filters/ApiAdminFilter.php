<?php

namespace App\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\Config\Services;

class ApiAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if the user has admin acess
        if (!auth()->user()->can('admin.access') and !auth()->user()->tokenCan('admin.access')) {
            $response = Services::response();
            $response->setStatusCode(403);
            $response->setJSON([
                'status' => 403,
                'error' => 403,
                'messages' => ['error' => 'Forbidden'],
            ]);
            return $response;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
