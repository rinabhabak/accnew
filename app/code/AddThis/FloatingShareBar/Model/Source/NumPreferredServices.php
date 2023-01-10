<?php

namespace AddThis\FloatingShareBar\Model\Source;

class NumPreferredServices implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        $arr = range(1, 10);
        $ret = [];
        foreach ($arr as $value) {
            $ret[] = [
                'value' => $value,
                'label' => (string)$value
            ];
        }
        return $ret;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $arr = range(1, 10);
        $ret = [];
        foreach ($arr as $value) {
            $ret[] = [
                (string)$value => __(string($value))
            ];
        }
        return $ret;
    }
}
