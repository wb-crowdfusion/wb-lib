<?php
/**
 *
 * @package     wb-lib
 * @version     $Id: $
 *
 */

class WbadsonarFilterer extends AbstractFilterer
{
    protected $pid = '';
    public function setWbadsonarPid($pid)
    {
        $this->pid = $pid;
    }
    public function pid()
    {
        return $this->pid;
    }

    protected $ps = '-1';
    public function setWbadsonarPs($ps)
    {
        $this->ps = $ps;
    }
    public function ps()
    {
        return $this->ps;
    }

    protected $jv = 'ads.adsonar.com';
    public function setWbadsonarJv($jv)
    {
        $this->jv = $jv;
    }
    public function jv()
    {
        return $this->jv;
    }
}
