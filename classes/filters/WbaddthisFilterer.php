<?php
/**
 *
 * @package     wb-lib
 * @version     $Id: $
 *
 */

class WbaddthisFilterer extends AbstractFilterer
{
    protected $pubId = '';
    public function setWbaddthisPubId($pubId)
    {
        $this->pubId = $pubId;
    }
    public function pubId()
    {
        return $this->pubId;
    }
}
