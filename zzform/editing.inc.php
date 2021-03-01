<?php 

/**
 * Zugzwang Project
 * Editing functions
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mf_contacts_edit_contact_name($fields) {
	return trim($fields['persons.first_name'])
		.($fields['persons.name_particle'] ? ' '.trim($fields['persons.name_particle']) : '')
		.' '.trim($fields['persons.last_name']);
}
