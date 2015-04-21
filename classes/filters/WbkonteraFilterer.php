<?php
/**
 *
 * @package     wb-lib
 * @version     $Id: $
 *
 */

class WbkonteraFilterer extends AbstractFilterer
{
    protected $publisherId = '';
    public function setWbkonteraPublisherId($publisherId)
    {
        $this->publisherId = $publisherId;
    }
    public function publisherId()
    {
        return $this->publisherId;
    }

    protected $adLinkColor = 'blue';
    public function setWbkonteraAdLinkColor($adLinkColor)
    {
        $this->adLinkColor = $adLinkColor;
    }
    public function adLinkColor()
    {
        return $this->adLinkColor;
    }

    protected $isBoldActive = 'no';
    public function setWbkonteraIsBoldActive($isBoldActive)
    {
        $this->isBoldActive = $isBoldActive;
    }
    public function isBoldActive()
    {
        return $this->isBoldActive;
    }
}
