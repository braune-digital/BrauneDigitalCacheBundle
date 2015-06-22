<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrauneDigital\CacheBundle\HttpCache;

use BrauneDigital\CacheBundle\Adapter\FileSystemCache;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;


abstract class ProxyHttpCache extends HttpCache
{
	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if ($this->getKernel()->getEnvironment() != 'dev') {
			$cache = new FileSystemCache($this->getKernel());
			$key = md5($request->getRealMethod() . '-' . $request->getPathInfo());
			if ($cache->has($key)) {
				return new Response($cache->get($key), 200);
			}
		}
		return parent::handle($request, $type, $catch);
	}


}
