<?php
/**
 * Create console commands for export model / Alpine_Cogs
 *
 * @category    Alpine
 * @package     Alpine_Cogs
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Evgeniy Derevyanko (evgeniy.derevyanko@alpineinc.com)
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Cogs\Console\Command;

use Alpine\Cogs\Cron\ExportCSV;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Psr\Log\LoggerInterface;
use Magento\Framework\App\State;

/**
 * ExecuteExport - console commands implementation class
 *
 * @category    Alpine
 * @package     Alpine_Cogs
 */
class ExecuteExport extends Command
{
    /**
     * logic model
     * 
     * @var ExportCSV
     */
    private $exportCSV;

    /**
     * logger
     * 
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * state
     *
     * @var State 
     */
    protected $appState;
    
    /**
     * console commands implementation class constructor
     * 
     * @param LoggerInterface $logger
     * @param ExportCSV $exportCSV
     * @param State $appState
     */
    public function __construct(
        LoggerInterface $logger,
        ExportCSV $exportCSV,
        State $appState
    ) {
        $this->logger       = $logger;
        $this->exportCSV    = $exportCSV;
        $this->appState     = $appState;
        parent::__construct();
    }

    /**
     * configure console commands method
     */
    protected function configure()
    {
        $this->setName('alpine:export:start')
             ->setDescription('export: start export to csv');
        $this->logger->info('configure: RUN SUCCESS');
        parent::configure();
    }

    /**
     * console command execute method
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info('Console command execute: RUN SUCCESS');
        $this->appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_ADMINHTML,
            [$this->exportCSV, 'execute']
        );
    }
}