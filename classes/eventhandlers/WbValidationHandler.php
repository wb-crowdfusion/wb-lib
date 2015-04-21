<?php
/**
 * Provides some generic validation methods that can be added to
 * other plugins' event bindings
 *
 * @package     wb-lib
 * @version     $Id: WbValidationHandler.php 603 2011-07-20 04:10:33Z 12dnetworks $
 *
 */

class WbValidationHandler
{
    public function requirePrimaryImage(Errors &$errors, NodeRef $nodeRef, Node &$node)
    {
        $element = $nodeRef->getElement();
        $schema  = $element->getSchema();
        if (!PartialUtils::isTagRoleInOutTagsScope($schema, $node->getNodePartials(), '#primary-image'))
        {
            // this should only occur during bulk cms actions
            return;
        }

        if ($schema->hasTagDef('#primary-image'))
        {
            $primaryImageTag = $node->getOutTag('#primary-image');
            $errors->rejectIfEmpty('#primary-image', 'tag', 'Primary Image', $primaryImageTag);
        }
    }

    /*
     * Rejects a meta field if it contains embedded kaltura video code.
     * this forces people to use the fields and short code [[video]] rather
     * than having blobs of kaltura code in meta fields.
     *
     * modified apr 25, 2012 to look for "entry_id" instead of "uiconf" so that for
     * the most part only site specific videos are rejected, i.e. ones where the embed
     * code was copied directly from the kmc and not via kaltura's fuck you share options.
     *
     * when users are embedding videos from external sites, using the share options from
     * the player it won't have an "entry_id" setting, instead some other random shit
     * like wid (which is normally, you know a widget id) is actually a magic pointer
     * to the entry id.  so fuck you.
     *
     */
    public function rejectInlineKaltura(Errors &$errors, Meta &$meta)
    {
        if (stristr((string)$meta->getValue(), 'kaltura') !== false && stristr((string)$meta->getValue(), 'entry_id') !== false) {
            $errors->reject('You cannot embed Kaltura videos inline, use the provided video fields and input [[video]] into the contents instead.');
        }
    }

    /*
     * Rejects a meta field if it contains embedded listtool code.
     * this forces people to use the fields and short code [[listtool]]
     *
     */
    public function rejectInlineListTool(Errors &$errors, Meta &$meta)
    {
        if (stristr((string)$meta->getValue(), '://listtool') !== false) {
            $errors->reject('You cannot embed the List Tool inline, use the provided fields and input [[listtool]] into the contents instead.');
        }
    }

    /*
     * Rejects a meta field if it contains embedded poll code.
     * this forces people to use the fields and short code [[poll]]
     *
     */
    public function rejectInlinePoll(Errors &$errors, Meta &$meta)
    {
        if (stristr((string)$meta->getValue(), '://cdn.polls') !== false || stristr((string)$meta->getValue(), '://polls') !== false || stristr((string)$meta->getValue(), 'wb-polls/') !== false) {
            $errors->reject('You cannot embed Polls inline, use the provided fields and input [[poll]] into the contents instead.');
        }
    }

    /*
     * Rejects a meta field if it contains embedded survey code.
     * this forces people to use the fields and short code [[survey]]
     *
     */
    public function rejectInlineSurvey(Errors &$errors, Meta &$meta)
    {
        if (stristr((string)$meta->getValue(), '://www.surveymonkey') !== false) {
            $errors->reject('You cannot embed Surveys inline, use the provided fields and input [[survey]] into the contents instead.');
        }
    }

    /*
     * Rejects a meta field if it contains embedded spreecast player.
     * this forces people to use the fields and short code [[spreecast]]
     *
     */
    public function rejectInlineSpreecast(Errors &$errors, Meta &$meta)
    {
        if (stristr((string)$meta->getValue(), 'iframe id="spreecast-player"') !== false) {
            $errors->reject('You cannot embed Spreecast players inline, use the provided fields and input [[spreecast]] into the contents instead.');
        }
    }
}