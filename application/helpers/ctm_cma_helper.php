<?php

/**
 * Parses the criteria member of the codes var
 * in config/settings.php.  Returns array of table
 * descriptions keyed by table code
 *
 * @param array $criteria
 * @return array:
 */
function extract_tables($criteria = array())
{
	$tables = array();

	foreach ($criteria as $table => $info)
	{
		$tables[$table] = $info['desc'];
	}

	return $tables;
}

/**
 * Combines outlet and article master field
 * references into one array key'd by code
 * 
 * @param array $criteria
 * @return array
 */
function extract_fields($criteria = array())
{
	$fields = array();
	
	foreach ($criteria as $table => $info)
	{
		foreach ($info['fields'] as $code => $name)
		{
			$fields[$code] = $name;
		}
	}
	
	return $fields;
}

/**
 * Checks string to see if it is currency
 * 
 * @param string $currency
 * @return bool
 */
function is_currency($currency)
{
	if (preg_replace("/^[-+]?\d*(\.(\d{2}))?$/", '', $currency) == '')
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

/**
 * Takes user's assigned locs and filters all
 * locations (as returned by xi_model) against 
 * them.  Returns associative array containing
 * only locations user has access too.
 * 
 * @param array $user_locs
 * @param array $all_locs
 * @return array:
 */
function restrict_locs($user_locs = array(), $all_locs = array())
{
	return array_intersect_key($all_locs, array_flip($user_locs));
}

/**
 * Wrapper for date_parse_from format.  Converts formatted date string
 * from one format to another
 * 
 * @param string $date Date to convert
 * @param string $format Current date format
 * @param string $new_format Desired date format
 * 
 * @return string|bool
 */
function conv_date($date = NULL, $format = 'Y-m-d', $new_format = 'Y-m-d')
{
	$pdate = date_parse_from_format($format, $date);
	
	// make sure date is valid
	if (checkdate($pdate['month'], $pdate['day'], $pdate['year']))
	{
		return date($new_format, mktime($pdate['hour'], $pdate['minute'], $pdate['second'], $pdate['month'], $pdate['day'], $pdate['year']));
	}
	else // bad date, return false
	{
		return false;
	}
}

/**
 * Takes two dates in 'Y-m-d' format.  Returns years covered by
 * date range in array
 * 
 * @param string $start
 * @param string $end
 * @return array $years
 */
function calc_date_range_years($start, $end)
{
	$years = array();
	
	$start_prts = explode('-', $start);
	$end_prts = explode('-', $end);
	
	$years[] = $start_prts[0];
	
	for ($i=$start_prts[0]; $i<$end_prts[0]; $i++)
	{
		$years[] = $i + 1;
	}
	
	return $years;
}

/**
 * Takes date in specified format and converts to unix time
 * 
 * @param string $date
 * @param string $format
 * @return number|boolean
 */
function conv_date_unix($date = NULL, $format = 'Y-m-d')
{
	$pdate = date_parse_from_format($format, $date);
	
	// make sure date is valid
	if (checkdate($pdate['month'], $pdate['day'], $pdate['year']))
	{
		return mktime($pdate['hour'], $pdate['minute'], $pdate['second'], $pdate['month'], $pdate['day'], $pdate['year']);
	}
	else // bad date, return false
	{
		return false;
	}
}