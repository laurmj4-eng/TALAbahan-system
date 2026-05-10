<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ActivityLogFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $activityLogModel = new \App\Models\ActivityLogModel();
        $session = session();

        // Get URI object
        $uri = $request->getUri();
        $path = $uri->getPath();

        // Skip logging if it's the debug toolbar or other common assets that might trigger filters
        if (strpos($path, 'debugbar') !== false) {
            return;
        }

        $data = [
            'user_id'    => $session->get('user_id'),
            'user_role'  => $session->get('role') ?? 'guest',
            'action'     => $request->getMethod(true) . ' Request',
            'url_path'   => $path,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => (string) $request->getUserAgent(),
        ];

        try {
            $activityLogModel->insert($data);
        } catch (\Exception $e) {
            // Silently fail to not interrupt user experience if logging fails
            log_message('error', '[ActivityLogFilter] ' . $e->getMessage());
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
