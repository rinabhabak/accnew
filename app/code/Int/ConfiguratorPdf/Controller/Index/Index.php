<?php

namespace Int\ConfiguratorPdf\Controller\Index;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Int\ConfiguratorPdf\Helper\Data as ConfiguratorPdfHelper;


class Index extends \Magento\Framework\App\Action\Action
{
    protected $_configuratorPdfHelper;
    
    /**
     * Constructor
     *
     * @param Context $context
     * @param ConfiguratorPdfHelper $configuratorPdfHelper
     *
     */
    
    public function __construct(
        Context $context,        
        ConfiguratorPdfHelper $configuratorPdfHelper
    ) {
        $this->_configuratorPdfHelper = $configuratorPdfHelper;       
        parent::__construct($context);
    }

    public function getHtmlForPdf() {
        return '
        <html>
            <head>
                <meta charset="utf-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <title>Page Title</title>
                <meta name="viewport" content="width=device-width, initial-scale=1">
            </head>
            <body>
                <table style="width: 700px; margin: auto; font-family: sans-serif;">
                    <tr>
                        <td colspan="2" style="text-align: right; font-size: 40px; padding-bottom: 60px;">
                            logo
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-size: 40px; color: #333;">
                            Bill of Material
                        </td>
                        <td style="text-align: right; font-size: 18px;">
                            <p style="font-size: 22px; color: #333; margin: 0; padding: 0;">Date: 10-23-2020</p>
                            <p style="font-size: 22px; color: #333; margin: 0; padding: 0;">ID Number: xxxxxxxxx</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-size: 40px; vertical-align: bottom; color: #333; padding-top: 50px; padding-bottom: 50px;">
                            <p style="font-size: 30px; color: #222; margin: 0; padding: 0;">Senseon</p>
                            <p style="font-size: 22px; color: #333; margin: 0; padding: 0;">
                                12311 Shoemaker Avenue <br/>
                                Santa Fe Springs, CA 90670
                            </p>
                        </td>
                        <td style="text-align: left; font-size: 18px; vertical-align: bottom; padding-top: 50px; padding-bottom: 50px;">
                            <p style="font-size: 22px; color: #333; margin: 0; padding: 0;">To: Chris Bell</p>
                            <p style="font-size: 22px; color: #333; margin: 0; padding: 0;">Email: Chris@mail.com</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="margin: 0; padding: 0; padding-top: 50px; border-top: 1px #333 solid;">
                            <table style="width: 700px; margin: auto; border: 1px #333 solid; border-collapse: collapse;">
                                <tr>
                                    <th style="border: 1px #333 solid; text-align: left; font-size: 20px; color: #666; font-weight: 300; padding: 5px;">Senseon Part#</th>
                                    <th style="border: 1px #333 solid; font-size: 20px; color: #666; font-weight: 300; padding: 5px;">Rev</th>
                                    <th style="border: 1px #333 solid; font-size: 20px; color: #666; font-weight: 300; padding: 5px;">UOM</th>
                                    <th style="border: 1px #333 solid; font-size: 20px; color: #666; font-weight: 300; padding: 5px;">Qty</th>
                                </tr>
                                <tr>
                                    <td style="border: 1px #333 solid; height: 300px;"></td>
                                    <td style="border: 1px #333 solid; height: 300px;"></td>
                                    <td style="border: 1px #333 solid; height: 300px;"></td>
                                    <td style="border: 1px #333 solid; height: 300px;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 20px; color: #333; margin: 0; padding: 0; padding-top: 50px; padding-bottom: 50px; line-height: 30px;">
                            Thank you for building your System Configurator! Your project information has been sent to our team! They will reach out to you to confirm you have everything you need to secure your cabinet
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 20px; color: #333; margin: 0; padding: 0; padding-top: 50px; border-top: 1px #333 solid; line-height: 30px;">
                            All customer orders to Accuride are subject to written acceptance in the form of Accuride’s customer acknowledgement with general terms and conditions set forth on the back. Any additional or any conflicting terms and conditions set forth in the Buyer’s purchase order shall be null and void and shall not form part of any contract between Accuride and the Buyer.
                        </td>
                    </tr>
                </table>
            </body>
            </html>';
    }
    
    

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $html = $this->getHtmlForPdf();
        echo $this->_configuratorPdfHelper->generatePdf($html);

    }
    
}
