<?php
/**
 *
 * @package     wb-lib
 * @version     $Id: $
 *
 */

class WbtyntFilterer extends AbstractFilterer
{
    protected $id = '';
    public function setWbtyntId($id)
    {
        $this->id = $id;
    }
    public function id()
    {
        return $this->id;
    }
}
