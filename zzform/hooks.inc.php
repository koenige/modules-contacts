<?php 

/**
 * contacts module
 * Hook functions
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * contacts hook after update
 *
 * @param array $ops
 * @return array
 */
function mf_contacts_contact_update($ops) {
	global $zz_conf;
	global $zz_setting;
	static $calls;

	// keine mehrfachen Aufrufe, s. u.
	if (!$calls) $calls = 0;
	$calls++;
	if ($calls > 1) return [];

	$p_index = false;
	$c_index = false;
	$ci_index = [];

	foreach ($ops['return'] as $index => $table) {
		if ($table['table'] === 'personen') $p_index = $index;
		if ($table['table'] === 'contacts') $c_index = $index;
		if ($table['table'] === 'contacts_identifiers') $ci_index[] = $index;
	}
	
	if (!empty($zz_conf['referer'])) {
		// set referer to new identification if it changed
		$zz_conf['int']['url']['qs_zzform'] = str_replace($ops['record_old'][$c_index]['identifier'], $ops['record_new'][$c_index]['identifier'], $zz_conf['int']['url']['qs_zzform']);
	}
	return [];
}
