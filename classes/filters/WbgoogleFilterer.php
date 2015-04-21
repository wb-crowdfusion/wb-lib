<?php
/**
 *
 * @package     wb-lib
 * @version     $Id: $
 *
 */

class WbgoogleFilterer extends AbstractFilterer
{
    protected $analyticsAccount = '';
    public function setWbgoogleAnalyticsAccount($analyticsAccount)
    {
        $this->analyticsAccount = $analyticsAccount;
    }
    public function analyticsAccount()
    {
        return $this->analyticsAccount;
    }
}
