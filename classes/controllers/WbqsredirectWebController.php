<?php
/**
 * Handles redirects for permalink pages that relied on
 * query string params to function.  Can't use a simple
 * redirects rule because that ALWAYS uses QSA and
 * that's not what we want in all cases.
 *
 * @package     wb-lib
 * @version     $Id: WbqsredirectWebController.php 603 2011-07-20 04:10:33Z 12dnetworks $
 *
 */

class WbqsredirectWebController extends AbstractWebController
{
    protected function handle()
    {
        $qsa = StringUtils::strToBool((string)$this->getTemplateVariable('QSA'));
        $destination = $this->getRequiredTemplateVariable('Destination');
        $isPermanent = StringUtils::strToBool((string)$this->getTemplateVariable('IsPermanent'));
        $sc = $isPermanent ? Response::SC_MOVED_PERMANENTLY : Response::SC_OK;

        if ($qsa) {
            $qs = $this->Request->getQueryString();
            if (!empty($qs)) {
                if (strpos($destination, '?') === false) {
                    $destination .= '?' . $qs;
                } else {
                    $destination .= '&' . $qs;
                }
            }
        }

        $this->Response->addHeader('Cache-Control', 'no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->Response->addHeader('Expires', $this->DateFactory->newLocalDate()->toRFCDate());
        $this->Response->sendStatus($sc)->sendRedirect($destination);
    }
}