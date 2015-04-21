<?php
/**
 * Provides a datasource that will return only unique nodes for a
 * given request.  Useful for home pages where a small number of
 * elements are rendered from various sources but have a high
 * probability of being duplicated.
 *
 * In order for this to work properly all templates must call
 * this datasource and must be cached together.
 *
 * not sure I like that this is extending the sticky controller
 * might need to refactor that at some point.
 * todo: review dependence on sticky controller
 *
 * @package     wb-lib
 * @version     $Id:  $
 *
 */

class WbuniqueWebController extends WbstickyWebController
{
    private $existingItems = array();

    protected function items()
    {
        $maxRows = intval((string)$this->getRequiredTemplateVariable('MaxRows'));
        if ($maxRows < 1 || $maxRows > 25)
            $maxRows = 10;

        // we add the current # of items we've already loaded
        // to reduce the possible number of queries.  better to have
        // a slighty larger query than 10+ queries
        $queryMaxRows = $maxRows + count($this->existingItems);
        $this->templateVars['MaxRows'] = $queryMaxRows;

        $nodeCount = 0;
        $queryCount = 1;
        $finalItems = array();

        // echo 'max rows: ' . $maxRows . PHP_EOL;
        // echo 'query max rows: ' . $queryMaxRows . PHP_EOL;

        do {
            $items = parent::items();

            if (!is_array($items))
                break;

            // echo 'query count: ' . $queryCount . PHP_EOL;
            // echo 'items count: ' . count($items) . PHP_EOL;

            foreach ($items as $item)
            {
                if (!in_array($item->getNodeRef()->getRefURL(), $this->existingItems))
                {
                    // echo 'item not loaded yet' . PHP_EOL;
                    $finalItems[] = $item;
                    $nodeCount++;
                    if ($nodeCount >= $maxRows)
                        break;
                }
                else
                {
                    // echo 'item already loaded: ' . $item->getNodeRef()->getRefURL() . PHP_EOL;
                }
            }

            // if the number of items returned was less than the query max rows
            // then we must abort this loop as the query has no more records to return
            if (count($items) < $queryMaxRows)
                break;

            // make sure second run doesn't look at stickies
            $this->templateVars['StickyMax'] = 0;

            // update the offset so the next query
            // will get the next page of results.
            $queryCount++;
            $this->templateVars['Offset'] = ($queryCount * $queryMaxRows) - $queryMaxRows;

            // echo 'node count: ' . $nodeCount . PHP_EOL;

        } while ($nodeCount < $maxRows);

        // now add all of the nodes we found to the existing
        // items array so future calls don't return them
        foreach ($finalItems as $item)
        {
            // echo $item->getNodeRef()->getRefURL() . PHP_EOL;
            $this->existingItems[] = $item->getNodeRef()->getRefURL();
        }

        // echo 'nodes found: ' . $nodeCount . PHP_EOL . PHP_EOL;
        // echo '---------------' . PHP_EOL . PHP_EOL;

        $this->templateVars['TotalRecords'] = $nodeCount;
        $this->templateVars['TotalPages'] = 1;

        return $finalItems;
    }
}