<?php
/**
 * Functions to help provide the proper output for
 * iLoop feeds.
 *
 * @package     wb-lib
 * @version     $Id: WbiloopFilterer.php 603 2011-07-20 04:10:33Z 12dnetworks $
 *
 */

class WbiloopFilterer extends AbstractFilterer
{
    protected $InputClean;
    public function setInputClean(InputCleanInterface $InputClean) {
        $this->InputClean = $InputClean;
    }

    protected function getDefaultMethod()
    {
        return 'format';
    }

    /*
     * Formats the input by removing html and also ensures
     * it has nice readable line breaks.
     */
    public function format()
    {
        $this->allowTemplateCode();
        $str = (string) $this->getRequiredParameter('value');

        // InputClean might be all you need but
        // if not, do more stuff after
        $str = $this->InputClean->clean($str, 'p,br');
        $str = $this->InputClean->unAutoParagraph($str);

        // mutant characters inserted by the wysiwyg.
        // a combo capital A, circumflex and non-breaking space
        $tincyMCEHack = chr(194) . chr(160);
        $str = str_replace($tincyMCEHack, '', $str);

        $str = preg_replace("/(\r\n){3,}|(\n|\r){3,}/", "\n\n", $str);
        return trim($str);
    }
}