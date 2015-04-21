<?php
/**
 * Provides date related functionality not already supplied by the
 * crowdfusion date filterer.
 *
 *
 * @package     wb-lib
 * @version     $Id: WbdisplayFilterer.php 603 2011-07-20 04:10:33Z 12dnetworks $
 *
 */

class WbdateFilterer extends AbstractFilterer
{
    protected $DateFactory;
    public function setDateFactory(DateFactory $DateFactory)
    {
        $this->DateFactory = $DateFactory;
    }

    /*
     * Returns the end of the episode week based
     * on the cut over day/time. For example on friday
     * after 5pm the week end is next friday instead
     * of this friday.
     *
     */
    public function episodeWeekEnd()
    {
        $endDay  = (string) $this->getRequiredParameter('endDay'); // sun,mon,tue,wed,thu,fri,sat
        $endTime = (string) $this->getRequiredParameter('endTime'); // H:i:s

        $now = $this->DateFactory->newLocalDate();
        $today = strtolower($now->format('D'));

	    if ($endDay == $today) {
            $weekEnd = $now;
            if ($now > $this->DateFactory->newLocalDate($now->format('Y-m-d') . ' ' . $endTime))
                $weekEnd = $now->modify('+1 week');
	    } else {
            $weekEnd = $now->modify('next ' . $endDay);
        }
	    return $weekEnd->format('Y-m-d');
    }

    public function getAge() {
        $birthday = $this->getParameter('birthday');
        if (empty($birthday)) return '';

        if ($birthday instanceOf Meta) {
            $birthday = $birthday->MetaValue;
        } elseif (!$birthday instanceOf Date) {
            try {
                $date = $this->DateFactory->newLocalDate($birthday);
            } catch(DateException $e) {
                return '';
            }
        }

        $birthday = $this->DateFactory->toLocalDate($birthday);
        $now = $this->DateFactory->newLocalDate();
        $age = $now->diff($birthday);
        return $age->y;
    }

    public function secondsToHms() {
        $seconds = (string) $this->getRequiredParameter('seconds'); // 0-999999
        $hms = "";

        if( intval($seconds) < 60 ) {
            return '0:' . str_pad( $seconds, 2, "0", STR_PAD_LEFT );
        }
        $hours = intval( intval($seconds) / 3600 );
        if( $hours > 0 ) $hms .= $hours . ':';
        $minutes = intval( ( $seconds / 60 ) % 60 );
        $hms .= ($hours > 0 ? str_pad( $minutes, 2, "0", STR_PAD_LEFT ) . ':' : $minutes . ':');
        $seconds = intval( $seconds % 60 );
        $hms .= str_pad( $seconds, 2, "0", STR_PAD_LEFT );

        return $hms;
    }

    public function diff() {
        $date1 = $this->getRequiredParameter('date1');
        $date2 = $this->getRequiredParameter('date2');
        $interval = $this->getRequiredParameter('interval');
        if (empty($date1) && empty($date2)) return '';

        $interval = trim(strtolower($interval));
        if (!in_array($interval, explode(',', 'y,m,d,h,i,s,days')))
            throw new Exception('interval must be one of y,m,d,h,i,s,days');

        // need to use the "days" interval to get the absolute number
        // of days between date1 and date2.  once the dates span a month
        // or more "d" is reset (x months + x days).  days = the total
        if ($interval == 'd')
            $interval = 'days';

        if ($date1 instanceOf Meta) {
            $date1 = $date1->MetaValue;
        } elseif (!$date1 instanceOf Date) {
            try {
                $date1 = $this->DateFactory->newLocalDate($date1);
            } catch(DateException $e) {
                return '';
            }
        }

        if ($date2 instanceOf Meta) {
            $date2 = $date2->MetaValue;
        } elseif (!$date2 instanceOf Date) {
            try {
                $date2 = $this->DateFactory->newLocalDate($date2);
            } catch(DateException $e) {
                return '';
            }
        }

        $date1 = $this->DateFactory->toLocalDate($date1);
        $date2 = $this->DateFactory->toLocalDate($date2);
        $diff = $date1->diff($date2);
        return (int) ($diff->format('%R') . $diff->$interval);
    }

    /*
     * Provides a date modify function
     *
     * Expected params:
     *  value   string|date anything that can be passed to newLocalDate()
     *  modify  string      the modification to make, e.g. +1 week, tomorrow, etc.
     *  format  string      date format to return
     *
     */
    public function modify()
    {
        $date   = $this->getParameter('value');
        $modify = (string) $this->getParameter('modify');
        $format = (string) $this->getParameter('format');

        if (empty($date))
            $date = 'now';

        if ($date instanceof Meta)
            $date = $date->MetaValue;

        if (!$date instanceof Date) {
            try {
                $date = $this->DateFactory->newLocalDate($date);
            } catch(DateException $e) {
                $date = $this->DateFactory->newLocalDate('now');
            }
        }

        $date = $this->DateFactory->toLocalDate($date);
        if (!empty($modify)) {
            try {
                $date->modify($modify);
            } catch (Exception $e) {
                return '';
            }
        }

        return $date->format($format);
    }
}