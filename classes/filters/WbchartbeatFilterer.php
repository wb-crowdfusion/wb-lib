<?php
/**
 *
 * @package     wb-lib
 * @version     $Id: $
 *
 */

class WbchartbeatFilterer extends AbstractFilterer
{
    protected $uid = '';
    public function setWbchartbeatUid($uid)
    {
        $this->uid = $uid;
    }
    public function uid()
    {
        return $this->uid;
    }

    protected $domain = '';
    public function setWbchartbeatDomain($domain)
    {
        $this->domain = $domain;
    }
    public function domain()
    {
        return $this->domain;
    }
}
