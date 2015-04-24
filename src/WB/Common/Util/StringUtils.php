<?php

namespace WB\Common\Util;

class StringUtils
{
    /**
     * Converts the input supplied to a safe xml version that can be included in
     * xml attributes and nodes without the use of CDATA.
     *
     * @param string $str
     * @return string
     */
    public static function xmlEscape($str)
    {
        // array used to figure what number to decrement from character order value
        // according to number of characters used to map unicode to ascii by utf-8
        $decrement[4] = 240;
        $decrement[3] = 224;
        $decrement[2] = 192;
        $decrement[1] = 0;

        // the number of bits to shift each charNum by
        $shift[1][0] = 0;
        $shift[2][0] = 6;
        $shift[2][1] = 0;
        $shift[3][0] = 12;
        $shift[3][1] = 6;
        $shift[3][2] = 0;
        $shift[4][0] = 18;
        $shift[4][1] = 12;
        $shift[4][2] = 6;
        $shift[4][3] = 0;

        $pos = 0;
        // using standard strlen and letting loop determine # of bytes-per-char
        $len = strlen($str);
        $xml = '';

        while ($pos < $len) {
            $asciiPos = ord(substr($str, $pos, 1));
            if (($asciiPos >= 240) && ($asciiPos <= 255)) {
                // 4 chars representing one unicode character
                $thisLetter = substr($str, $pos, 4);
                $pos += 4;
            } else if (($asciiPos >= 224) && ($asciiPos <= 239)) {
                // 3 chars representing one unicode character
                $thisLetter = substr ($str, $pos, 3);
                $pos += 3;
            } else if (($asciiPos >= 192) && ($asciiPos <= 223)) {
                // 2 chars representing one unicode character
                $thisLetter = substr($str, $pos, 2);
                $pos += 2;
            } else {
                // 1 char (lower ascii)
                $thisLetter = substr($str, $pos, 1);
                $pos += 1;
            }

            // process the string representing the letter to a unicode entity
            $thisLen = strlen ($thisLetter);
            $thisPos = 0;
            $decimalCode = 0;
            while ($thisPos < $thisLen) {
                $thisCharOrd = ord(substr($thisLetter, $thisPos, 1));
                if ($thisPos == 0) {
                    $charNum = intval($thisCharOrd - $decrement[$thisLen]);
                    $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
                } else {
                    $charNum = intval($thisCharOrd - 128);
                    $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
                }

                $thisPos++;
            }

            if ($thisLen == 1) {
                $encodedLetter = '&#'. str_pad($decimalCode, 3, '0', STR_PAD_LEFT) . ';';
            } else {
                $encodedLetter = '&#'. str_pad($decimalCode, 5, '0', STR_PAD_LEFT) . ';';
            }

            $c = $decimalCode;


            if ($c > 0 && $c < 32) {
                $xml .= $encodedLetter;
            } else if ($c >= 32 && $c < 127) {
                switch ($thisLetter) {
                    case '<':
                        $xml .= '&lt;';
                        break;
                    case '>':
                        $xml .= '&gt;';
                        break;
                    case '&':
                        $xml .= '&amp;';
                        break;
                    case '"':
                        $xml .= '&quot;';
                        break;
                    default:
                        $xml .= $thisLetter;
                }
            } else {
                $xml .= $encodedLetter;
            }
        }

        // final mutant possibility (the amped amp)
        $xml = str_replace('&amp;amp;', '&amp;', $xml);
        return trim($xml);
    }
}