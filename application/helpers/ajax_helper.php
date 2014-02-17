<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Returns true if http request is XHR
 * 
 * @return boolean
 */
function is_ajax()
{
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}

/**
 * Takes associative array and converts it to 
 * jquery ui autocomplete friendly format, json encoded
 * 
 * @param array $values Must be keyed: value => display
 * @param boolean $use_id If set to true, label is assigned to both value and label fields, and value is sent over as "id"
 * @return array
 */
function jquery_autocomp_format($values = array(), $use_val = FALSE)
{
	$ac_friendly = array();
	
	foreach ($values as $value => $label)
	{
		// are we including id property?
		if ($use_val)
		{
			// display goes in lable and value fields, 
			// value goes in val field
			$ac_friendly[] = array(
				'label' => $label,
				'value' => $label,
				'val' => $value
			);
		}
		else
		{
			$ac_friendly[] = array(
				'label' => $label,
				'value' => $value
			);
		}
	}
	
	// encode into json and return
	return json_encode($ac_friendly);
}