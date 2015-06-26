<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrauneDigital\CacheBundle\Cache;

use BrauneDigital\CacheBundle\Adapter\FileSystemCache;
use FOS\HttpCacheBundle\CacheManager;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Process\Process;


class QueueManager
{

	protected $dir = 'braunedigitalCacheQueue';

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @var CacheManager
	 */
	protected $cacheManager;

	/**
	 * @param Container $container
	 * @param CacheManager $cacheManager
	 */
	public function __construct(Container $container) {
		$this->container = $container;
		$this->cacheManager = $this->container->get('fos_http_cache.cache_manager');
		$this->dir = $this->container->get('kernel')->getRootDir() . '/' . $this->dir;
		$this->file = $this->dir . '/' . 'queue.txt';
	}

	/**
	 * @param $path
	 */
	public function addPath($path) {

		if (!file_exists($this->dir)) {
			mkdir($this->dir);
		}
		file_put_contents($this->file, $path . "\n", FILE_APPEND);
	}

	/**
	 *
	 */
	public function processQueue() {
		try {
			$paths = array();
			$handle = fopen($this->file, "r");
			if ($handle) {
				while (($line = fgets($handle)) !== false) {
					$paths[] = trim($line);
				}
				fclose($handle);
			}

			file_put_contents($this->file, '');
			foreach ($paths as $path) {
				$this->cacheManager->invalidatePath($path);
				$this->cacheManager->refreshPath($path);
			}
		} catch (\Exception $e) {}
	}

	public function triggerProcess() {
		$config = $this->container->getParameter('braune_digital_cache');
		$process = new Process($config['php_executable'] . ' ' . $this->container->get('kernel')->getRootDir() . '/console braunedigital:cache:processqueue');
		$process->disableOutput();
		$process->run();
	}

}
