<?php
/**
 * Provides event filter function that allows for locals to be
 * passed through to the transport.
 *
 *
 * @package     wb-lib
 * @version     $Id: $
 *
 */

class WbeventFilterer extends AbstractFilterer
{
    protected $Events = null;
    public function setEvents(Events $Events)
    {
        $this->Events = $Events;
    }

    protected function getDefaultMethod()
    {
        return 'filter';
    }

    /*
     * Announces the event and optionally passes through locals
     * as params.
     *
     */
    public function filter()
    {
        $eventName = $this->getRequiredParameter('name');
        $locals = explode(',', (string) $this->getParameter('PassLocals'));
        $this->allowTemplateCode();

        $params = $this->getParameters();
        $output = new Transport();

        foreach($locals as $local) {
            $output->$local = $this->getLocal($local);
        }

        foreach($params as $k => $v) {
            $output->$k = $v;
        }

        $this->Events->trigger($eventName, $output);
        return $output->value;
    }
}
