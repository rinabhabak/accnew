<?php
namespace Int\ConfiguratorPdf\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Dompdf
     *
     * @var \Dompdf\Dompdf
     */
    protected $_dompdf;
    
    
    /**
     * File System
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * Scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */    
    protected $_scopeConfig;
    
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
   
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Dompdf\Dompdf
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Dompdf\Dompdf $domPdf
        
    ) {
        $this->_dompdf          = $domPdf;
        $this->_filesystem      = $filesystem;
        $this->_scopeConfig     = $scopeConfig;
        $this->_storeManager    = $storeManager;
        $this->_directory       = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        parent::__construct($context);
    }


    /**
     * Generate Pdf
     * @param string $html
     * @param string $filename
     * @return string
     */
    
    public function generatePdf($html,$filename='')
    {
        $filename = 'Bill_of_Material-'.$filename.'.pdf';
		$this->_dompdf->set_option("isPhpEnabled", true);
        $this->_dompdf->loadHtml($html);
        $this->_dompdf->render();        
        $output = $this->_dompdf->output();
        $pdfDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('configurator_bom/');
        
        file_put_contents($pdfDirectory.$filename, $output);
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'configurator_bom/'.$filename;

    }
    
    
    /*
     * Get logo
     */
    
    public function getLogo(){
        $folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $storeLogoPath = $this->_scopeConfig->getValue(
            'design/header/logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $storeLogoPath;
        return $this->getMediaUrl(). $path;
    }
    
    
    /**
     * Return the Media URL
     *
     * @return string
     */
    public function getMediaUrl()
    { 
        return $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                );
    }
}
