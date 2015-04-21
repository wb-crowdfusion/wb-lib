<?php
/**
 * Provides misc. functionality for templates.  Functions small
 * enough that don't warrant their own controller.
 *
 * @package     wb-lib
 * @version     $Id: WbstickyWebController.php 603 2011-07-20 04:10:33Z 12dnetworks $
 *
 */

class WblibWebController extends NodeWebController
{
    /*
     * returns the requested result set in
     * reverse order.
     *
     */
    protected function reverseItems()
    {
        $items = parent::items();
        if (is_array($items))
            return array_reverse($items);

        return $items;
    }

    /*
     * used to mock a for loop in a cft by setting
     * the loop start/end.
     *
     */
    protected function forLoop()
    {
        $start = intval((string)$this->getRequiredTemplateVariable('LoopStart'));
        $end = intval((string)$this->getRequiredTemplateVariable('LoopEnd'));
        $items = array();

    	if ($start > $end) {
            for ($i = $start; $i >= $end; $i--)
                $items[] = array('ForIndex' => $i);
        } else {
            for ($i = $start; $i <= $end; $i++)
                $items[] = array('ForIndex' => $i);
        }

        $this->templateVars['TotalRecords'] = count($items);
        return $items;
    }

    /*
     * Creates an items array by exploding the
     * Items string.
     *
     */
    protected function explodeItems()
    {
        $str = trim((string)$this->getRequiredTemplateVariable('Items'));
        $delim = trim((string)$this->getTemplateVariable('Delimiter'));

        if (empty($str))
            return array();

        if (empty($delim))
            $delim = ',';

        $finalItems = array();
        $itemsArray = explode($delim, $str);
        foreach($itemsArray as $item)
        {
            $finalItems[] = array('Item' => trim($item));
        }

        $this->templateVars['TotalRecords'] = count($finalItems);
        return $finalItems;
    }
}