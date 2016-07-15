<?php

namespace Pumukit\MoodleBundle\Routing;

use Symfony\Component\Config\Loader\Loader as BaseLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


/**
 * Routing Loader to redirect naked backoffice domain to the main domain.
 */
class Loader extends BaseLoader
{
    private $loaded = false;
    private $nakedBackofficeDomain;
    private $mainDomain;

    function __construct($nakedBackofficeDomain, $mainDomain)
    {
        $this->nakedBackofficeDomain = $nakedBackofficeDomain;
        $this->mainDomain = $mainDomain;

        if (!$nakedBackofficeDomain || !$mainDomain) {
            $this->loaded = true;
        }
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            return new RouteCollection();
        }

        $routes = new RouteCollection();

        // prepare a new route
        $path = '/';
        $defaults = array(
            '_controller' => 'FrameworkBundle:Redirect:urlRedirect',
            'path'        => 'http://' . $this->mainDomain,
            'permanent'   => true,
        );
        $requirements = array();
        $options = array();
        $route = new Route($path, $defaults, $requirements, $options, $this->nakedBackofficeDomain);

        // add the new route to the route collection
        $routeName = 'pumukit_moodle_redirct_naked';
        $routes->add($routeName, $route);

        $this->loaded = true;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'moodle_redirect' === $type;
    }
}
