<?php
/**
 * Provides some workaround for handling variables in CFT.
 * Primarily the inability to conditionally set a variable.
 *
 *
 * @package     wb-lib
 * @version     $Id: $
 *
 */

class WbvarsFilterer extends AbstractFilterer
{
    private $setOnceVars = array();

    /*
     * sets a variable once only if the supplied
     * value is not considered "empty"
     */
    public function setOnce()
    {
        $varName = $this->getOnceVarName();
        $value = $this->getRequiredParameter('value');
        if (!empty($value) && !array_key_exists($varName, $this->setOnceVars))
            $this->setOnceVars[$varName] = $value;
    }

    /*
     * returns the value of a variable that was set
     * using setOnce.  returns null if the variable
     * doesn't exist.
     */
    public function getOnce()
    {
        $varName = $this->getOnceVarName();
        if (array_key_exists($varName, $this->setOnceVars))
            return $this->setOnceVars[$varName];
    }

    /*
     * return the "name" parameter as a valid
     * var name or die
     */
    private function getOnceVarName()
    {
        $name = (string) $this->getRequiredParameter('name');
        $varName = preg_replace('/[^a-zA-Z0-9_]+/i', '', $name);
        if (empty($varName))
            throw new Exception('variable name [' . $name . '] is invalid');

        return $varName;
    }

    /*
     * returns true if the value of the global (ref by name)
     * matches the value passed in.
     *
     * this was implemented because of a bug present in the template
     * engine that doesn't evaluate if statements on globals within
     * independent sub templates when the parent is cached.
     *
     */
    public function compareGlobal()
    {
        $global = trim((string)$this->getRequiredParameter('name'));
        return ($this->getGlobal($global) == $this->getRequiredParameter('value'));
    }
}