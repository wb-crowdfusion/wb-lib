<?php
class WblayoutFilterer extends AbstractFilterer
{
    /* @var Request */
    protected $request;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Converts a list of request (query, control, etc.) vars into a string that
     * is used to ensure a unique cache key on a template that is loaded from a
     * route and rendered with layout-renderer.cft
     *
     * @return string
     */
    protected function cachingAttributes()
	{
        $cacheAttributes = trim((string) $this->getParameter('value'));
        if (empty($cacheAttributes)) {
            return '';
        }

        $cacheAttributes = explode(',', $cacheAttributes);
        $cacheVals = array();

        foreach ($cacheAttributes as $name) {
            $requestVal = $this->request->getParameter($name);
            if (empty($requestVal)) {
                continue;
            }

            if (is_scalar($requestVal)) {
                $requestVal = trim($requestVal);
                if (empty($requestVal)) {
                    continue;
                }
                $cacheVals[trim(strtolower($name))] = $requestVal;
            } elseif (is_array($requestVal)) {
                try {
                    $cacheVals[trim(strtolower($name))] = implode(',', $requestVal);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return http_build_query($cacheVals, '', '|');
	}
}