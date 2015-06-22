<?php

namespace BrauneDigital\CacheBundle\Adapter;


use Symfony\Component\HttpKernel\Kernel;

class FileSystemCache implements AdapterInterface {

	/**
	 * @var Kernel
	 */
	protected $kernel;

	/**
	 * @param Kernel $kernel
	 */
	public function __construct(Kernel $kernel) {
		$this->kernel = $kernel;
	}

	/**
	 * @param $key
	 * @return boolean
	 */
	public function has($key)
	{
		if (file_exists($this->getCacheDir() . '/' . $key)) {
			return true;
		}
		return false;
	}

	/**
	 * @param $key
	 * @param $content
	 * @return boolean
	 */
	public function store($key, $content)
	{
		if (!file_exists($this->getCacheDir())) {
			mkdir($this->getCacheDir());
		}
		file_put_contents($this->getCacheDir() . '/' . $key, $content);
		return true;
	}

	/**
	 * @param $key
	 * @return string
	 */
	public function get($key)
	{
		return file_get_contents($this->getCacheDir() . '/' . $key);
	}

	/**
	 * @return string
	 */
	public function getCacheDir()
	{
		return $this->kernel->getCacheDir().'/braunedigital_cache';
	}


}

?>