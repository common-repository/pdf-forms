<?php
namespace PdfFormsLoader\Core;

use Symfony\Component\HttpFoundation\Request;

class CustomRequest
{
    private static $instance = null;
    protected $request;

    public static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->request = (new Request())::createFromGlobals();
    }

    /**
     * @param null|string $paramName
     * @return mixed|\Symfony\Component\HttpFoundation\ParameterBag
     */
    public function get($paramName = null)
    {
        if (empty($paramName)) {
            return $this->request->query;
        }

        return $this->request->query->get($paramName);
    }

    /**
     * @param null|string $paramName
     * @return mixed|\Symfony\Component\HttpFoundation\ParameterBag
     */
    public function post($paramName = null)
    {
        if (empty($paramName)) {
            return $this->request->request;
        }
        return $this->request->request->get($paramName);
    }

    /**
     * Return post params if post type or get params if not
     * @param null|string $paramName
     * @return mixed|\Symfony\Component\HttpFoundation\ParameterBag
     */
    public function postOrGet($paramName = null)
    {
        if ($this->request->isMethod('POST')) {
            return $this->post($paramName);
        }

        return $this->get($paramName);
    }

    /**
     * @param null $paramName
     * @return mixed|\Symfony\Component\HttpFoundation\ServerBag
     */
    public function server($paramName = null)
    {
        if (empty($paramName)) {
            return $this->request->server;
        }
        return $this->request->server->get($paramName);
    }
}
