<?php

class WbdisplayFilterer extends AbstractFilterer
{
    /** @var InputClean */
    protected $inputClean;

    /**
     * @param InputClean $val
     */
    public function setInputClean(InputClean $val)
    {
        $this->inputClean = $val;
    }

    /**
     * Displays the contents of value but allows for template code.
     *
     * Expected params:
     * - value      string to process.
     *
     * @return string
     */
    public function dynamic()
    {
        $this->allowTemplateCode();
        return $this->getParameter('value');
    }

    /**
     * Formats a number using php builtin number_format
     *
     * @link http://php.net/manual/en/function.number-format.php
     *
     * Expected params:
     * - number     number
     * - decimals   number|null (number of decimal places to render)
     *
     * @return string
     */
    public function numberFormat()
    {
        $num = (float) ((string) $this->getRequiredParameter('number'));
        $decimals = intval((string)$this->getParameter('decimals'));
        return number_format($num, $decimals);
    }

    /**
     * Automatically adds <p> tags to content with line breaks
     *
     * Expected params:
     * - value          string - to process
     * - allowedTags    string - comma delimited list of tags to allow, e.g. a[href],b,p
     * - linkUrls       bool   - whether or not links should be auto converted to a tags.
     *
     * @return string
     */
    public function autoParagraph()
    {
        $str = $this->getParameter('value');
        $linkUrls = null === $this->getParameter('linkUrls') ? true : \StringUtils::strToBool($this->getParameter('linkUrls'));
        return $this->inputClean->autoParagraph($str, $this->getParameter('allowedTags'), $linkUrls);
    }
}