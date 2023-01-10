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

namespace SLI\Feed\Model;

use Magento\Framework\Flag;

/**
 * Manage feed generation state.
 */
class GenerateFlag extends Flag
{
    /**
     * There was no generation
     */
    const STATE_INACTIVE = 0;

    /**
     * Generate process is active
     */
    const STATE_RUNNING = 1;

    /**
     * Generation finished
     */
    const STATE_FINISHED = 2;

    /**
     * Generation finished and notify message was formed
     */
    const STATE_NOTIFIED = 3;

    /**
     * Flag time to life in seconds
     */
    const FLAG_TTL = 7200; // 2 hours

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        // unique code for this flag
        $this->setFlagCode('sli:generate');
        parent::_construct();
    }

    /**
     * Set error to flag
     *
     * @param \Exception $e
     * @return $this
     */
    public function setError(\Exception $e)
    {
        $data = $this->getFlagData();
        if (!is_array($data)) {
            $data = [];
        }

        $data['has_errors'] = true;
        $data['exception'] = $e->getMessage();

        $this->setFlagData($data)->save();

        return $this;
    }

    /**
     * Check if we are currently locked.
     *
     * @return bool
     */
    public function isLocked()
    {
        return static::STATE_RUNNING == $this->getState() && !$this->isTimeout();
    }

    /*
     * Check the life span of the generate flag.
     *
     * @return bool
     */
    public function isTimeout()
    {
        // GMT time in 'Y-m-d H:i:s' format from 'magento.flag.last_update'
        $lastUpdateFromDb = $this->getLastUpdate();

        // no entry found
        if (!$lastUpdateFromDb) {
            return false;
        }

        // check is timeout limit reach
        $lastUpdateDt = new \DateTime($lastUpdateFromDb, new \DateTimeZone('GMT'));

        return time() > ($lastUpdateDt->getTimestamp() + static::FLAG_TTL);
    }

    /**
     * Lock generate flag.
     *
     * @return $this
     */
    public function lock()
    {
        $this->setState(static::STATE_RUNNING)->setFlagData([])->save();

        return $this;
    }

    /**
     * Release generate flag.
     *
     * @param array $results
     * @param \DateTime|null $datetime
     * @return $this
     */
    public function release(array $results = [], $datetime = null)
    {
        $data = $this->getFlagData();
        if (!is_array($data)) {
            $data = [];
        }

        if (!isset($data['message'])) {
            $data['message'] = '';
        }

        if ($datetime) {
            $data['message'] .= sprintf('<p><small><strong>Last Run Status on %s</strong></small></p>', $datetime->format('Y-m-d H:i:s (e)'));
        } else {
            $data['message'] .= sprintf('<p><small><strong>Last Run Status</strong></small></p>');
        }

        $data['message'] .= '<ul style="margin-left: 2em;">';
        foreach ($results as $storeId => $meta) {
            $data['message'] .= sprintf('<li><small>%s: %s</small></li>', $meta['name'], $meta['status']);
        }
        $data['message'] .= '</ul>';

        $this->setState(static::STATE_FINISHED)->setFlagData($data)->save();

        return $this;
    }
}
