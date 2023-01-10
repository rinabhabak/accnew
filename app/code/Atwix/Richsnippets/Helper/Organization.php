<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2016 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */
namespace Atwix\Richsnippets\Helper;

use Atwix\Richsnippets\Helper\Data as SnippetsHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Url as Url;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Organization
 */
class Organization extends SnippetsHelper
{

    protected $socialUrls = [
        'gplus'     => 'https://plus.google.com/',
        'facebook'  => 'https://www.facebook.com/',
        'linkedin'  => 'https://www.linkedin.com/company/',
        'pinterest' => 'https://www.pinterest.com/',
        'instagram' => 'https://instagram.com/',
        'twitter'   => 'https://twitter.com/'
    ];

    protected $organizationInfo = [
        'address' => [
            'postalCode'      => '',
            'addressLocality' => '',
            'streetAddress'   => ''
        ],
        'email'     => '',
        'name'      => '',
        'telephone' => '',
        'logo'      => ''
    ];

    /**
     * Generates organization snippets in JSON format
     *
     * @return string
     */
    public function makeOrganizationJson()
    {
        $jsonSnippet = array();
        $jsonSnippet['@context'] = 'http://schema.org';
        $jsonSnippet['@type'] = 'Organization';
        $jsonSnippet['address']['@type'] = 'PostalAddress';
        $jsonSnippet['url'] = $this->storeManager->getStore()->getBaseUrl();

        /* Add organization info to the snippet */
        $jsonSnippet = array_merge($jsonSnippet, $this->getOrganizationInfo());

        /* Get social links */
        $socialLinks = [];

        foreach ($this->socialUrls as $socialLinkName => $socialLinkUrl) {
            $socialLink = $this->getSocialLink($socialLinkName);
            if ($socialLink) {
                $socialLinks[] = $this->getSocialLink($socialLinkName);
            }
        }

        if (count($socialLinks) > 0) {
            $jsonSnippet['sameAs'][] = $socialLinks;
        }

       // if($this->getConfigurationValue('snippets/system/rating')) {
            // TODO: implement rating system in further versions
       // }

        return json_encode($jsonSnippet);
    }

    /**
     * Collects the necessary organization info from the system configuration
     *
     * @return array
     */
    protected function getOrganizationInfo()
    {
        $orgInfo = [];
        foreach ($this->organizationInfo as $infoFieldName => $infoFieldValue) {
            if (is_array($infoFieldValue)) {
                foreach ($infoFieldValue as $infoSubfieldName => $infoSubfieldValue) {
                    $configValue = $this->getConfigurationValue('organization/' . $infoSubfieldName);
                    if (empty($configValue)) {
                        continue;
                    }
                    $orgInfo[$infoFieldName][$infoSubfieldName] = $configValue;
                }
            } else {
                $configValue = $this->getConfigurationValue('organization/' . $infoFieldName);
                if (!$configValue) {
                    continue;
                }
                $orgInfo[$infoFieldName] = $configValue;
            }
        }

        return $orgInfo;
    }

    /**
     * Returns social link with username
     *
     * @param $socialType
     * @return null|string
     */
    protected function getSocialLink($socialType)
    {
        $socialEnabled = $this->getConfigurationValue('social/' . $socialType);
        $socialUsername = $this->getSocialUsername($socialType);
        if ($socialEnabled && !empty($socialUsername)) {
            return $this->socialUrls[$socialType] . $socialUsername;
        } else {
            return null;
        }
    }

    /**
     * Returns a social network username from the system configuration
     *
     * @param $socialType
     * @return string
     */
    protected function getSocialUsername($socialType)
    {
        return $this->getConfigurationValue('social/' . $socialType . '_username');
    }
}