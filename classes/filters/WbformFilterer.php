<?php
class WbformFilterer extends AbstractFilterer
{
    /*
     * Renders various common form elements
     *
     */
	protected $states = array(
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'DC' => 'District Of Columbia',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming'
	);

    public function stateOptions()
    {
    	$selected = strtoupper(trim((string) $this->getParameter('selected')));

    	$options = '';
    	if (!empty($selected))
    	{
    		foreach ($this->states as $stateCode => $stateName)
    		{
    			if ($selected == $stateCode) {
    				$options .= '<option value="'.$stateCode.'" selected>'.$stateName.'</option>';
    			} else {
    				$options .= '<option value="'.$stateCode.'">'.$stateName.'</option>';
    			}
    		}
    	} else {
    		foreach ($this->states as $stateCode => $stateName)
    		{
    			$options .= '<option value="'.$stateCode.'">'.$stateName.'</option>';
    		}
    	}
        return $options;
    }

    public function numericalOptions()
    {
    	$min      = (int) $this->getRequiredParameter('min');
    	$max      = (int) $this->getRequiredParameter('max');
    	$selected = (int) $this->getParameter('selected');

    	if ($min > $max)
    		throw new Exception('min value must be less than max: ('.$min.', '.$max.')');

    	$options = '';
   		for ($i = $min; $i <= $max; $i++)
   		{
   			if ($selected == $i) {
   				$options .= '<option value="'.$i.'" selected>'.$i.'</option>';
   			} else {
   				$options .= '<option value="'.$i.'">'.$i.'</option>';
   			}
   		}
        return $options;
    }

    public function monthOptions()
    {
    	$format   = (string) $this->getParameter('format'); // number, short, long
        $selected = (int) $this->getParameter('selected');

        $formats = array('number', 'short', 'long');
        if (!in_array($format, $formats))
            $format = 'number';

        if ($format != 'number') {
            $cal = cal_info(CAL_GREGORIAN);
            if ($format == 'short') {
                $months = $cal['abbrevmonths'];
            } else {
                $months = $cal['months'];
            }
        } else {
            $months = array();
            for ($i = 1; $i <= 12; $i++)
            {
                $months[$i] = str_pad($i, 2, '0', STR_PAD_LEFT);
            }
        }

    	$options = '';
   		for ($i = 1; $i <= 12; $i++)
   		{
            $ipad = str_pad($i, 2, '0', STR_PAD_LEFT);
   			if ($selected == $i) {
   				$options .= '<option value="'.$ipad.'" selected>'.$months[$i].'</option>';
   			} else {
   				$options .= '<option value="'.$ipad.'">'.$months[$i].'</option>';
   			}
   		}
        return $options;
    }
}