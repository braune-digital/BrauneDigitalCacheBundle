<?php

namespace BrauneDigital\CacheBundle\EventListener;

use BrauneDigital\CacheBundle\Adapter\FileSystemCache;
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
	 * @param FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event) {

		$route = $event->getRequest()->get('_route');
		$cache = new FileSystemCache($this->container->get('kernel'));
		if ($this->isCacheable($route)) {
			$key = md5($event->getRequest()->getRealMethod() . '-' . $event->getRequest()->getPathInfo());
			$cache->store($key, $event->getResponse()->getContent());
		}
	}

	/**
	 * @param $route
	 * @return bool
	 */
	public function isCacheable($route) {

		if ($this->container->get('kernel')->getEnvironment() == 'dev') {
			return false;
		}

		$config = $this->container->getParameter('braunedigital_cache');

		$match = array_filter($config['routes'], function($r) use($route) {
			if ($r['enabled']) {
				if (preg_match($r['route'], $route)) {
					return true;
				} else {
					return false;
				}
			}
		});

		if (count($match) > 0) {
			return true;
		}
		return false;

	}

}