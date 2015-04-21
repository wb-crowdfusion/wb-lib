<?php
/**
 * Provides functions for getting a list of nodes that are sticky
 * first and then the remaining nodes up to the list size.
 *
 * Note: Sticky is not to be implemented past page 1 at this time.
 *
 * Sticky Reference:
 * https://www.assembla.com/wiki/show/crowd-fusion-telepictures/Sticky
 *
 * @package     wb-lib
 * @version     $Id: WbstickyWebController.php 603 2011-07-20 04:10:33Z 12dnetworks $
 *
 */

class WbstickyWebController extends NodeWebController
{
    private $passThruVars = array(
            'Slugs.in', 'Elements.in', 'Meta.select', 'OutTags.select', 'InTags.select',
            'OutTags.exist', 'InTags.exist', 'Meta.exist',
            'Title.like', 'Title.ieq', 'Title.eq', 'Title.firstChar',
            'ActiveDate.before', 'ActiveDate.after', 'ActiveDate.start', 'ActiveDate.end',
            'CreationDate.before', 'CreationDate.after', 'CreationDate.start', 'CreationDate.end'
        );

    protected function items()
    {
        $page = intval((string)$this->getTemplateVariable('Page'));
        if ($page > 1)
           return parent::items();

        // apparently the orderBy isn't set when passthruTemplateVariable
        // is used so we'll set it directly on the NodeQuery
        $orderBy = (string)$this->getTemplateVariable('OrderBy');
        if (empty($orderBy))
            $orderBy = 'ActiveDate DESC';

        $stickyOutTags = $this->getTemplateVariable('StickyOutTags.exist');
        $stickyMax = intval((string)$this->getTemplateVariable('StickyMax'));

        // i guess someone might want to turn da stickies off.  :(
        if ($stickyMax < 1 || empty($stickyOutTags))
           return parent::items();

        if ($stickyMax > 10)
            $stickyMax = 10;

        $maxRows = intval((string)$this->getRequiredTemplateVariable('MaxRows'));
        if ($maxRows < 1 || $maxRows > 50)
            $maxRows = 10;

        $stickyMax = min($stickyMax, $maxRows);

        // pass thru all template settings to the node query
        $nq = new NodeQuery();
        foreach ($this->passThruVars as $var)
            $this->passthruTemplateVariable($nq, $var);
        $this->setMetaWhereParams($nq);
        $nq->setLimit($stickyMax);
        $nq->isRetrieveTotalRecords(false);
        $nq->setParameter('Status.isActive', true);
        $nq->setOrderBy($orderBy);
        $nq->setParameter('OutTags.exist', $stickyOutTags);

        // get the sticky-in nodes (up to StickyMax)
        $items = $this->RegulatedNodeService->findAll($nq)->getResults();

        // return early if sticky count satisfies the list size
        $stickiesFound = 0;
        if (is_array($items)) {
            $stickiesFound = count($items);
        } else {
            $items = array();
        }

        $nodeCountToFill = $maxRows - $stickiesFound;
        if ($nodeCountToFill < 1)
            return $items;

        // loop through other nodes and add them if they're
        // not already in the final list, up to the maxrows
        $nq = new NodeQuery();
        foreach ($this->passThruVars as $var)
            $this->passthruTemplateVariable($nq, $var);
        $this->setMetaWhereParams($nq);
        $nq->setLimit($maxRows + $stickiesFound);

        // since all of our listing pages are 10 or greater rows
        // it makes no sense to get total count when we are
        // requesting less than 10 as that means we're getting
        // items for a module that doesn't require paging.
        $nq->isRetrieveTotalRecords($maxRows >= 10);

        $nq->setParameter('Status.isActive', true);
        $nq->setOrderBy($orderBy);
        $this->buildLimitOffset($nq);

        $nonStickyItems = $this->RegulatedNodeService->findAll($nq)->getResults();
        if (is_array($nonStickyItems)) {
            foreach ($nonStickyItems as $item) {
                if ($nodeCountToFill == 0)
                    break;
                if (!in_array($item, $items)) {
                    $items[] = $item;
                    $nodeCountToFill--;
                }
            }

            $this->templateVars['TotalRecords'] = $nq->getTotalRecords();
            $this->templateVars['TotalPages'] = intval(($nq->getTotalRecords()-1)/$nq->getLimit())+1;
        }

        return $items;
    }

    /*
     * sets the meta where params that exist in the calling
     * template on the node query.
     */
    private function setMetaWhereParams(&$nq)
    {
        foreach($this->templateVars as $name => $value)
        {
            if  (strpos($name, '#') === 0)
                $nq->setParameter($name, $value);
        }
    }
}