<?php 

/**
 * contacts module
 * definition helper functions for forms with zzform
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * create _contacts-subtable from categories
 *
 * @param array $zz
 * @param string $table prefix of table name *_contacts
 * @param array $def definition of table
 *		output of mf_default_categories_restrict()
 *		int category_id
 *		string category
 *		string path
 *		array parameters
 * @param int $no
 */
function mf_contacts_contacts_subtable(&$zz, $table, $def, $no) {
	$zz['fields'][$no] = zzform_include($table.'-contacts');
	$zz['fields'][$no]['title'] = $def['category'];
	$zz['fields'][$no]['table_name'] = str_replace('/*_PREFIX_*/', '', $zz['fields'][$no]['table']).'_'.$def['category_id'];
	$zz['fields'][$no]['type'] = 'subtable';
	$zz['fields'][$no]['min_records'] = $def['parameters']['min_records'] ?? 1;
	$zz['fields'][$no]['max_records'] = $def['parameters']['max_records'] ?? 20;
	$zz['fields'][$no]['sql'] .= sprintf(' WHERE role_category_id = %d
		ORDER BY sequence, /*_PREFIX_*/contacts.identifier', $def['category_id']);
	$zz['fields'][$no]['form_display'] = 'lines';
	$zz['fields'][$no]['fields'][2]['type'] = 'foreign_key';
	$zz['fields'][$no]['fields'][3]['show_title'] = false;
	$zz['fields'][$no]['fields'][3]['sql'] = sprintf('SELECT contact_id, contact
		FROM contacts
		LEFT JOIN categories
			ON contacts.contact_category_id = categories.category_id
		WHERE categories.parameters LIKE "%%&%s_%s=1%%"
		ORDER BY identifier', $table, $def['path']);
	$zz['fields'][$no]['fields'][3]['add_details'] = $def['parameters']['add_details'] ?? false;
	$zz['fields'][$no]['fields'][3]['select_dont_force_single_value'] = true;
	$zz['fields'][$no]['fields'][4]['type'] = 'hidden';
	$zz['fields'][$no]['fields'][4]['value'] = $def['category_id'];
	$zz['fields'][$no]['fields'][4]['hide_in_form'] = true;
	$zz['fields'][$no]['fields'][5]['type'] = 'sequence';
	if (!empty($def['parameters']['role'])) {
		$zz['fields'][$no]['fields'][6]['hide_in_form'] = false;
		$zz['fields'][$no]['fields'][6]['placeholder'] = true;
	}
	$zz['fields'][$no]['class'] = 'hidden480';
	if (!empty($zz['fields'][$no]['subselect'])) {
		$zz['fields'][$no]['unless']['export_mode']['subselect']['prefix'] = '<br><em>'.wrap_text($def['category']).'</em>: ';
		$zz['fields'][$no]['unless']['export_mode']['subselect']['suffix'] = '';
		if (empty($def['last_category']))
			$zz['fields'][$no]['unless']['export_mode']['list_append_next'] = true;
		$zz['fields'][$no]['subselect']['sql'] = wrap_edit_sql(
			$zz['fields'][$no]['subselect']['sql'], 'WHERE',
			sprintf('role_category_id = %d', $def['category_id'])	
		);
	}
	$zz['fields'][$no]['hide_in_list'] = $def['parameters']['hide_in_list'] ?? false;
}
