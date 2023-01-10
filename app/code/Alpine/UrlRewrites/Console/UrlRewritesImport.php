<?php
/**
 * Alpine_UrlRewrites
 *
 * @category    Alpine
 * @package     Alpine_UrlRewrites
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Alex Didenko <alex.didenko@alpineinc.com>
 */

namespace Alpine\UrlRewrites\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Alpine\UrlRewrites\Model\Import;

/**
 * Class UrlRewritesImport
 *
 * @category    Alpine
 * @package     Alpine_UrlRewritesCommand
 */
class UrlRewritesImport extends Command
{
    /**
     * Import url rewrite model
     *
     * @var Import
     */
    protected $import;

    /**
     * UrlRewritesImport constructor
     *
     * @param Import $import
     * @param string $name
     */
    public function __construct(
        Import $import,
        $name = null
    ) {
        $this->import = $import;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('alpine:url-rewrites:import');
        $this->setDescription('Creates url rewrites based on csv data');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(strtoupper('Url rewrites import started.') . "\n");
        $this->import->execute();
        $output->writeln(strtoupper('Url rewrites import finished.') . "\n");
    }
}
