<?php

namespace BrauneDigital\CacheBundle\Adapter;


use Symfony\Component\HttpKernel\Kernel;

interface AdapterInterface {

	/**
	 * @param Kernel $kernel
	 */
	public function __construct(Kernel $kernel);

	/**
	 * @param $key
	 * @return boolean
	 */
	public function has($key);

	/**
	 * @param $key
	 * @param $content
	 * @return boolean
	 */
	public function store($key, $content);

	/**
	 * @param $key
	 * @return string
	 */
	public function get($key);


}

?>