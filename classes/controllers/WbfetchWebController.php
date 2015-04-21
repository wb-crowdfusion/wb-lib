<?php
/**
 * Custom controller for fetching feeds and returning their
 * contents as arrays to the templates, similar to node-items
 *
 * @package     wb-lib
 * @version     $Id: WbfetchWebController.php 603 2011-07-20 04:10:33Z 12dnetworks $
 *
 */

class WbfetchWebController extends AbstractWebController
{
    protected $FeedParser;
    public function setFeedParser(FeedParser $FeedParser)
    {
        $this->FeedParser = $FeedParser;
    }

    public function items()
    {
        $feedURL      = (string)$this->getRequiredTemplateVariable('FeedURL');
        $nativeOrder  = StringUtils::strToBool((string)$this->getTemplateVariable('NativeOrder'));
        $includeImage = StringUtils::strToBool((string)$this->getTemplateVariable('IncludeImage'));
        $maxRows      = intval((string)$this->getRequiredTemplateVariable('MaxRows'));

        $items = array();
        if (!URLUtils::isUrl($feedURL))
            return $items;

        try {
            $feed = $this->FeedParser->parseFeed($feedURL, $nativeOrder);
            $i = 0;
            foreach($feed->get_items() as $item) {
                if ($i >= $maxRows)
                    break;

                if (trim($item->get_title()) == '')
                    continue;

                $itemDate = null;
                if ($item->get_date() != '')
                    $itemDate = $this->DateFactory->newStorageDate($item->get_date());

                $img = null;
                if ($includeImage) {
                    // first try to get it from the enclosure node itself
                    $tags = $item->get_item_tags('', 'enclosure');
                    if (is_array($tags)) {
                        foreach ($tags as $tag) {
                            if (!isset($tag['attribs']['']['type']))
                                continue;

                            if (strpos($tag['attribs']['']['type'], 'image') !== false) {
                                $img = isset($tag['attribs']['']['url']) ? $tag['attribs']['']['url'] : null;
                                break;
                            }
                        }
                    }

                    if (empty($img)) {
                        $enclosure = $item->get_enclosure();
                        if ($enclosure) {
                            $img = $enclosure->get_thumbnail();
                            if (empty($img))
                                $img = $enclosure->get_link();
                        }
                    }
                }

                $items[] = array(
                    'Permalink'   => $item->get_permalink(),
                	'Title'       => $item->get_title(),
                    'Description' => $item->get_description(),
                    'PublishDate' => $itemDate,
                    'Image'       => $img,
                );

                $i++;
            }
        } catch (FeedParserException $fpe) {
            $this->Logger->debug($fpe->getMessage());
        }

    	return $items;
    }
}
