<?php

/**
 * Converts ldap_get_entries returned resource to
 * associative array keyed on requested ldap fields
 *
 * @param resource $ldap_entries
 * @return array:
 */
function entries_to_assoc($ldap_entries)
{
	$assoc_entries = array();

	for ($i=0;$i<$ldap_entries['count'];$i++)
	{
		for ($j=0;$j<$ldap_entries[$i]['count'];$j++)
		{
			$assoc_entries[$i][$ldap_entries[$i][$j]] = $ldap_entries[$i][$ldap_entries[$i][$j]][0];
		}
	}

	return $assoc_entries;
}