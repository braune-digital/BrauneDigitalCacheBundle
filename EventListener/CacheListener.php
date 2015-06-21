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

    protected $container;

    public function __construct(ContainerInterface $container)
    {
		$this->container = $container;
    }

	public function onKernelRequest(GetResponseEvent $event) {

		$debug = in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'));
		$cache = $this->container->get('sonata.cache.memcached');
		$key = array('request_uri' => $event->getRequest()->getRequestUri());

		if ($cache->has($key)) {
			$event->setResponse(new Response($cache->get($key)->getData(), 200));
			$event->stopPropagation();
		}

	}

	public function onKernelResponse(FilterResponseEvent $event) {
		$cache = $this->container->get('sonata.cache.memcached');
		$key = array('request_uri' => $event->getRequest()->getRequestUri());
		if (!$cache->has($key)) {
			$content = $event->getResponse()->getContent();
			$cache->set($key, $content);
		}
	}

}