<?php
/**
 *
 * @package     wb-lib
 * @version     $Id: $
 *
 */

class WboutbrainFilterer extends AbstractFilterer
{
    protected $template = '';
    public function setWboutbrainTemplate($template)
    {
        $this->template = $template;
    }
    public function template()
    {
        return $this->template;
    }
}
