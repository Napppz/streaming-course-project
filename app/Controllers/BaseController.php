<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['form', 'url'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }
    
    /**
     * Check if the current user is logged in
     *
     * @return bool
     */
    protected function isLoggedIn()
    {
        return session()->get('isLoggedIn') === true;
    }
    
    /**
     * Check if the current user is an admin
     *
     * @return bool
     */
    protected function isAdmin()
    {
        return $this->hasRole(['admin', 'super_admin']);
    }

    /**
     * Check if the current user is a super admin
     *
     * @return bool
     */
    protected function isSuperAdmin()
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if the current user has one of the given roles
     *
     * @param string|array $roles
     * @return bool
     */
    protected function hasRole($roles)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $roles = (array) $roles;

        return in_array(session()->get('role'), $roles, true);
    }

    /**
     * Get the dashboard path for the current role
     *
     * @param string|null $role
     * @return string
     */
    protected function getDashboardPath(?string $role = null)
    {
        $role ??= session()->get('role');

        return in_array($role, ['admin', 'super_admin'], true)
            ? '/admin/dashboard'
            : '/user/dashboard';
    }

    /**
     * Build an absolute URL from the active request host.
     */
    protected function currentHostUrl(string $path): string
    {
        $uri = $this->request->getUri();
        $scheme = $this->request->getHeaderLine('X-Forwarded-Proto') ?: $uri->getScheme();
        $host = $this->request->getHeaderLine('X-Forwarded-Host') ?: $uri->getHost();
        $port = $this->request->getHeaderLine('X-Forwarded-Port') ?: $uri->getPort();
        $scheme = trim(explode(',', $scheme)[0]);
        $host = trim(explode(',', $host)[0]);

        if ($host === '') {
            return $path;
        }

        $authority = $host;
        if ($port !== '' && $port !== null && ! in_array($port, [80, 443], true)) {
            $authority .= ':' . $port;
        }

        return rtrim($scheme . '://' . $authority, '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * Get the current user ID
     *
     * @return int|null
     */
    protected function getCurrentUserId()
    {
        return session()->get('id');
    }
    
    /**
     * Get the current user data
     *
     * @return array
     */
    protected function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => session()->get('id'),
            'username' => session()->get('username'),
            'email' => session()->get('email'),
            'full_name' => session()->get('full_name'),
            'role' => session()->get('role')
        ];
    }
}
