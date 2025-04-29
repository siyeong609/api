<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Cors implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $origin = $request->getHeaderLine('Origin') ?: '*';

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        if ($request->getMethod() === 'options') {
            header('HTTP/1.1 200 OK');
            exit;
        }
    }

    public function after(RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE, PATCH');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
