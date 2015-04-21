<?php
/**
 * Provides functions for getting the neighboring elements based
 * on the activation dates.  This is for the "next/prev" posts
 * buttons.
 */

class WbneighborWebController extends NodeWebController
{
    /**
     * prevents multiple queries as the "next/prev" buttons
     * are rendered on the page twice.
     *
     * @var array
     */
    private $slugNeighbors = array();

    /**
     * Finds the next and previous post using the templates
     * SlugDate which is the date/time of the current post.
     *
     * there's a chance the next post may not exist so we'll
     * just fake it by pulling an older post so the front
     * end will always have a next/prev button
     *
     * @return mixed
     */
    protected function posts()
    {
        $slugDate = $this->getRequiredTemplateVariable('SlugDate');
        $slugRefURL = $this->getRequiredTemplateVariable('SlugRefURL'); // current post
        $slugRefURL = preg_replace('/[^a-zA-Z0-9_]+/i', '', $slugRefURL);

        if (!array_key_exists($slugRefURL, $this->slugNeighbors)) {
            $prevPost = $this->getNeighbor($slugDate, 'prev');
            if (!is_array($prevPost)) {
                $prevPost = $this->getNeighbor(new Date('-1 month'), 'next');
            }

            $nextPost = $this->getNeighbor($slugDate, 'next');
            if (!is_array($nextPost)) {
                $nextPost = $this->getNeighbor($slugDate->modify('-1 month'), 'next');
            }

            $this->slugNeighbors[$slugRefURL] = array_merge((array)$prevPost, (array)$nextPost);
        }

        return $this->slugNeighbors[$slugRefURL];
    }

    /**
     * gets a single neighboring item
     *
     * @param Date $slugDate
     * @param string $direction
     * @return mixed
     */
    private function getNeighbor(Date $slugDate, $direction = 'next')
    {
        if (strcasecmp($direction, 'next') == 0) {
            $activeDate = 'ActiveDate.after';
            $orderBy = 'ActiveDate ASC';
            $slugDate = $slugDate->modify('+1 minute');
        } else {
            $activeDate = 'ActiveDate.before';
            $orderBy = 'ActiveDate DESC';
            $slugDate = $slugDate->modify('-1 minute');
        }

        $nq = new NodeQuery();
        $this->passthruTemplateVariable($nq, 'Elements.in');
        $this->passthruTemplateVariable($nq, 'OutTags.exist');
        $this->passthruTemplateVariable($nq, 'Meta.select');
        $this->passthruTemplateVariable($nq, 'OutTags.select');
        $this->passthruTemplateVariable($nq, 'InTags.select');
        $this->setMetaWhereParams($nq);
        $nq->setParameter($activeDate, $slugDate);
        $nq->setParameter('Status.isActive', true);
        $nq->setLimit(1);
        $nq->isRetrieveTotalRecords(false);
        $nq->setOrderBy($orderBy);

        return $this->RegulatedNodeService->findAll($nq)->getResults();
    }

    /**
     * sets the meta where params that exist in the calling
     * template on the node query.
     *
     * @param NodeQuery $nq
     */
    private function setMetaWhereParams(NodeQuery $nq)
    {
        foreach($this->templateVars as $name => $value) {
            if (strpos($name, '#') === 0) {
                $nq->setParameter($name, $value);
            }
        }
    }
}