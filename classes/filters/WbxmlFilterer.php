<?php

class WbxmlFilterer extends AbstractFilterer
{
    /**
     * @return string
     */
    protected function getDefaultMethod()
    {
        return 'convert';
    }

    /**
     * Converts the input supplied to a safe xml version that can be included in xml attributes
     * and nodes without the use of CDATA.
     *
     * Expected params
     * - value  - string to be converted
     *
     * @return string
     */
    public function convert()
    {
        return \WB\Common\Util\StringUtils::xmlEscape((string) $this->getRequiredParameter('value'));
    }
}