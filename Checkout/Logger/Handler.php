<?php
namespace Odontoprev\Checkout\Logger;

use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
   /**
    * Logging level
    * @var int
    */
   protected $loggerType = Logger::NOTICE;
   
   /**
    * File name
    * @var string
    */
   public $fileName = '';
   
   
  public function __construct(
            DriverInterface $filesystem,
            \Magento\Framework\Filesystem $corefilesystem,
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
            $filePath = null
        ) {
            $this->_localeDate = $localeDate;
            $corefilesystem= $corefilesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR); 
            $logpath = $corefilesystem->getAbsolutePath('log/');

            $filename = 'checkoutPayment_'.Date('d_m_Y').'.log';

            $filepath = $logpath . $filename;
            $this->cutomfileName=$filepath;
            parent::__construct(
                $filesystem,
                $filepath
            );
  
           $this->setFormatter(new LineFormatter('%message%'.PHP_EOL,null,true));

        }
}