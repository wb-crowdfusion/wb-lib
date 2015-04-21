<?php
/**
 * Provides misc. functionality for templates.  Functions small
 * enough that don't warrant their own controller.
 *
 * @package     wb-lib
 * @version     $Id: WbstickyWebController.php 603 2011-07-20 04:10:33Z 12dnetworks $
 *
 */

class WblibCmsController extends AbstractCmsController
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
    private $_primaryImageEmbedOptions = array();

    public function setWblibImageNames(array $imageNames)
    {
        $this->_imageNames = $imageNames;
    }

    /*
     * Returns the primary image embed options as
     * a template friendly resultset.
     *
     * used by the @wb-mixin-primary-image-settings
     * #embed-primary-image
     *
     */
    protected function primaryImageEmbedOptions()
    {
        if (!count($this->_primaryImageEmbedOptions))
        {
            foreach ($this->_imageNames as $key => $image) {
                if (array_key_exists('Title', $image)) {
                    $title = $image['Title'];
                } else {
                    $title = SlugUtils::unsluggify($key);
                }

                $deprecated = false;
                if (array_key_exists('Deprecated', $image))
                    $deprecated = StringUtils::strToBool($image['Deprecated']);

                $size = '';
                if (array_key_exists('Size', $image))
                    $size = (string) $image['Size'];

                $this->_primaryImageEmbedOptions[] = array(
                        'EmbedOptionSlug' => $key,
                        'EmbedOptionTitle' => $title,
                        'EmbedOptionDeprecated' => $deprecated,
                        'EmbedOptionSize' => $size
                    );
            }

            $this->_primaryImageEmbedOptions[] = array(
                    'EmbedOptionSlug' => 'off',
                    'EmbedOptionTitle' => 'DO NOT Auto Embed',
                    'EmbedOptionDeprecated' => false,
                    'EmbedOptionSize' => ''
                );
        }

        return $this->_primaryImageEmbedOptions;
    }
}