<?php
/**
 *
 * @package     wb-lib
 * @version     $Id: $
 *
 */

class WbimageFilterer extends AbstractFilterer
{
    /**
     * [IoC] Sets the image names from pluginconfig
     *
     * @param array $imageNames
     *
     * @return void
     *
     */
    private $_imageNames = array();
    public function setWblibImageNames(array $imageNames)
    {
        $this->_imageNames = $imageNames;
    }

    protected function getDefaultMethod()
    {
        return 'getSize';
    }

    /*
     * Returns true if the image name has been
     * defined in pluginconfig
     *
     */
    public function nameExists()
    {
        $name = (string) $this->getRequiredParameter('name');
        if (empty($name))
            return false;

        return array_key_exists($name, $this->_imageNames);
    }

    /*
     * Returns the title for a named image.
     *
     */
    public function getTitle()
    {
        $name = (string) $this->getRequiredParameter('name');
        if (!$this->nameExists())
            return SlugUtils::unsluggify($name);

        $image = $this->_imageNames[$name];
        if (!array_key_exists('Title', $image))
            return SlugUtils::unsluggify($name);

        return $image['Title'];
    }

    /*
     * Returns true if the name isn't defined or
     * if the Deprecated key is false
     *
     */
    public function isDeprecated()
    {
        $name = (string) $this->getRequiredParameter('name');
        if (!$this->nameExists())
            return true;

        $image = $this->_imageNames[$name];
        if (array_key_exists('Deprecated', $image))
            return StringUtils::strToBool($image['Deprecated']);

        return false;
    }

    /*
     * Returns the size of a named image.  If the
     * image name isn't defined a default of 100x100
     * is returned.
     *
     * Returning 100x100 so there's no chance of a fatal
     * error if/when a named image doesn't exist.  Legacy
     * data or templates using old names should be
     * upgraded.
     *
     */
    public function getSize()
    {
        $name    = (string) $this->getRequiredParameter('name');
        $default = (string) $this->getParameter('default');

        if (empty($default))
            $default = '100x100';

        if (!$this->nameExists())
            return $default;

        $image = $this->_imageNames[$name];
        if (!array_key_exists('Size', $image))
            return $default;

        return $image['Size'];
    }

    /*
     * Returns the width of a named image.
     *
     */
    public function getWidth()
    {
        try {
            list($width) = explode('x', $this->getSize());
        } catch (Exception $e) {
            $width = '';
        }
        return $width;
    }

    /*
     * Returns the height of a named image.
     *
     */
    public function getHeight()
    {
        try {
            list(, $height) = explode('x', $this->getSize());
        } catch (Exception $e) {
            $height = '';
        }

        return $height;
    }
}
