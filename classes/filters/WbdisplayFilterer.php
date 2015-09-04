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

    /**
     * Returns a string to json and returns null if it's empty.
     *
     * params:
     * - value
     *
     * @return string
     */
    public function stringToJson()
    {
        $value = trim($this->getParameter('value'));
        if (!strlen($value)) {
            return 'null';
        }
        return json_encode($value);
    }

    /**
     * Returns an array to json.
     *
     * params:
     * - value
     *
     * @return string
     */
    public function arrayToJson()
    {
        $value = $this->getParameter('value');
        if (!is_array($value)) {
            $value = array($value);
        }
        return json_encode($value);
    }

    /**
     * Returns a string array to json and removes empty items.
     *
     * params:
     * - value - array or delimited string
     * - delimiter - string, defaults to ','
     * - lowercase - bool, defaults to false
     *
     * @return string
     */
    public function stringArrayToJson()
    {
        $value = $this->getParameter('value');
        if (!is_array($value)) {
            $value = explode($this->getParameter('delimiter', ','), $value);
        }

        $value = array_map('trim', $value);
        if (empty($value)) {
            return '[]';
        }
        $value = array_filter($value, 'strlen');

        if ($this->getParameter('lowercase', false)) {
            $value = array_map('strtolower', $value);
            if ($this->getParameter('unique', true)) {
                $value = array_keys(array_flip($value));
            }
        } else {
            if ($this->getParameter('unique', true)) {
                $value = array_values(array_intersect_key($value, array_unique(array_map('strtolower', $value))));
            }
        }

        return json_encode($value);
    }

    /**
     * Returns a string array to csv and removes empty items.
     *
     * params:
     * - value - array or delimited string
     * - delimiter - string, defaults to ","
     * - lowercase - bool, defaults to false
     *
     * @return string
     */
    public function stringArrayToCsv()
    {
        $value = json_decode($this->stringArrayToJson(), true);

        return implode(',', $value);
    }
}
