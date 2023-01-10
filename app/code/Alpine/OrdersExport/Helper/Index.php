<?php
/**
 * Alpine_OrdersExport Helper
 *
 * @category    Alpine
 * @package     Alpine_OrdersExport
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\OrdersExport\Helper;

/**
 * Alpine_OrdersExport Helper
 *
 * @category    Alpine
 * @package     Alpine_OrdersExport
 */
class Index
{
    /**
     * Send upload response as text CVS file
     *
     * @param $response
     * @param $fileName
     * @param $content
     * @param string $contentType
     * @return mixed
     */
    public function sendUploadResponse($response, $fileName, $content, $contentType = 'text/plain')
    {
        return $response
            ->setHttpResponseCode(200)
            ->setHeader('Content-Disposition', "attachment; filename=$fileName", true)
            ->setHeader('Content-Length', strlen($content), true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Transfer-Encoding', 'binary', true)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Expires', 0, true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Last-Modified', date('r'), true)
            ->setBody($content)
            ->sendResponse();
    }
}