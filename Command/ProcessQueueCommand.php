<?php

namespace BrauneDigital\CacheBundle\Command;

use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ProcessQueueCommand extends ContainerAwareCommand
{

	protected $em;
	protected $output;

	protected function configure()
	{
		$this
			->setName('braunedigital:cache:processqueue')
			->setDescription('Process Queue')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$this->queueManager = $this->getContainer()->get('braunedigital.cache.queue');
		$this->queueManager->processQueue();

	}
}