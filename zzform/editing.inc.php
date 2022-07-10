<?php 

/**
 * contacts module
 * Editing functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * put person’s name into contacts.contact
 *
 * @param array $fields
 * @return string
 */
function mf_contacts_edit_contact_name($fields) {
	return trim($fields['persons.first_name'])
		.($fields['persons.name_particle'] ? ' '.trim($fields['persons.name_particle']) : '')
		.' '.trim($fields['persons.last_name']);
}

/**
 * add display name from contact
 *
 * @param array $fields
 * @param string $field_name
 * @return string
 */
function mf_contacts_display_name($fields, $field_name) {
	switch ($field_name) {
	case 'contact_display_name':
		if (!empty($fields['contact_id'])) {
			$sql = 'SELECT contact FROM /*_PREFIX_*/contacts WHERE contact_id = %d';
			$sql = sprintf($sql, $fields['contact_id']);
		} elseif (!empty($fields['person_id'])) {
			$sql = 'SELECT contact
				FROM /*_PREFIX_*/persons
				LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
				WHERE person_id = %d';
			$sql = sprintf($sql, $fields['person_id']);
		}
		break;		
	default:
		return '';
	}
	$value = wrap_db_fetch($sql, '', 'single value');
	if (!$value) return '';
	return $value;
}
