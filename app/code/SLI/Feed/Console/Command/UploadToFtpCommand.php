<?php
/**
 * Copyright (c) 2015 S.L.I. Systems, Inc. (www.sli-systems.com) - All Rights Reserved
 * This file is part of Learning Search Connect.
 * Learning Search Connect is distributed under a limited and restricted
 * license – please visit www.sli-systems.com/LSC for full license details.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE. TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO
 * EVENT WILL SLI BE LIABLE TO YOU OR ANY OTHER PARTY FOR ANY GENERAL, DIRECT,
 * INDIRECT, SPECIAL, INCIDENTAL OR CONSEQUENTIAL LOSS OR DAMAGES OF ANY
 * CHARACTER ARISING OUT OF THE USE OF THE CODE AND/OR THE LICENSE INCLUDING
 * BUT NOT LIMITED TO PERSONAL INJURY, LOSS OF DATA, LOSS OF PROFITS, LOSS OF
 * ASSIGNMENTS, DATA OR OUTPUT FROM THE SERVICE BEING RENDERED INACCURATE,
 * FAILURE OF CODE, SERVER DOWN TIME, DAMAGES FOR LOSS OF GOODWILL, BUSINESS
 * INTERRUPTION, COMPUTER FAILURE OR MALFUNCTION, OR ANY AND ALL OTHER DAMAGES
 * OR LOSSES OF WHATEVER NATURE, EVEN IF SLI HAS BEEN INFORMED OF THE
 * POSSIBILITY OF SUCH DAMAGES.
 */

namespace SLI\Feed\Console\Command;

use Magento\Framework\App\State;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SLI\Feed\Helper\FTPUpload;
use SLI\Feed\Logging\LoggerFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UploadToFtpCommand
 *
 * @package SLI\Feed\Console\Command
 */
class UploadToFtpCommand extends Command
{
    /**
     * FTP Upload
     *
     * @var FTPUpload
     */
    protected $ftp;

    /**
     * Logger factory
     *
     * @var LoggerFactoryInterface
     */
    protected $loggerFactory;

    /**
     * @param FTPUpload $ftp
     * @param LoggerFactoryInterface $loggerFactory
     */
    public function __construct(FTPUpload $ftp, LoggerFactoryInterface $loggerFactory)
    {
        parent::__construct();
        $this->ftp = $ftp;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sli:feed:testftp')
            ->setDescription('Tests the upload capability of the LSC Feed Module')
            ->addArgument('storeId', InputArgument::REQUIRED, 'Store id.')
            ->addArgument('filePath', InputArgument::REQUIRED, 'A file to upload.');
        parent::configure();
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verbose = $input->getOption('verbose');

        if (!$input->getOption('quiet')) {
            // add (almost) output handler
            $handler = new StreamHandler('php://stdout', $verbose ? Logger::DEBUG : Logger::INFO);
            $this->loggerFactory->addLoggerHandler($handler, LoggerFactoryInterface::STORE_LOGGER);
        }

        $storeId = $input->getArgument('storeId');
        $filePath = $input->getArgument('filePath');

        if ($verbose) {
            $output->writeln('File to upload: ' . $filePath);
        }
        $this->ftp->writeFileToFTP($filePath, $storeId);
    }
}
