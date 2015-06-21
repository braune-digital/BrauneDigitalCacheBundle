<?php

namespace BrauneDigital\CacheBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Pagerfanta\Pagerfanta;
use Sonata\Cache\Adapter\Cache\PRedisCache;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouterInterface;


class CacheListener extends ContainerAware {

	/**
	 * @var ContainerInterface
	 */
    protected $container;


	/**
	 * @param ContainerInterface $container
	 */
    public function __construct(ContainerInterface $container)
    {
		$this->container = $container;

    }

	/**
	 * @param GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event) {

		$debug = in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'));

		$format = $event->getRequest()->getRequestFormat();

		$router = $this->container->get('router');
		$route = $router->match($event->getRequest()->getPathInfo());
		if ($this->isCacheable($route['_route'])) {
			$cache = $this->container->get('sonata.cache.memcached');
			$keys = array(
				'route' => $route['_route'],
				'request_uri' => $event->getRequest()->getRequestUri(),
				'env' => $this->container->get('kernel')->getEnvironment()
			);
			if ($cache->has($keys)) {
				$event->setResponse(new Response($cache->get($keys)->getData(), 200));
				$event->stopPropagation();
				//$cache->flushAll();


			}
		}

	}

	/**
	 * @param FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event) {

		$route = $event->getRequest()->get('_route');
		if ($this->isCacheable($route)) {
			$cache = $this->container->get('sonata.cache.memcached');
			$keys = array(
				'route' => $route,
				'request_uri' => $event->getRequest()->getRequestUri(),
				'env' => $this->container->get('kernel')->getEnvironment()
			);
			if ($this->isCacheable($route) && !$cache->has($keys)) {
				$content = $event->getResponse()->getContent();
				$cache->set($keys, $content);
			}
		}
	}

	/**
	 * @param $route
	 * @return bool
	 */
	public function isCacheable($route) {
		$config = $this->container->getParameter('braunedigital_cache');
		if (array_key_exists($route, $config['routes']) && $config['routes'][$route]['enabled']) {
			return true;
		}
		return false;

	}

}