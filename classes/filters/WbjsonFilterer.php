<?php

class WbjsonFilterer extends JsonFilterer
{
    /**
     * Indents a flat JSON string to make it more human-readable
     *
     * @return string
     */
    public function format()
    {
        $json = (string) $this->getParameter('value');
        if (empty($json)) {
            return '""';
        }

        $html = (boolean) $this->getParameter('html');
        return JSONUtils::format($json, $html);
    }
}
