<?php 

/**
 * Zugzwang Project
 * Form for contacts without persons
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$values = [];
$values['relations_restrict_to'] = 'contacts';

$zz = zzform_include_table('contacts', $values);
$zz['title'] = 'Contacts (without persons)';

$zz['sql'] .= 'WHERE categories.parameters LIKE "%&contacts_general=1%"';

$zz['fields'][4]['sql'] .= ' AND categories.parameters LIKE "%&contacts_general=1%"';

$zz['filter'][1]['sql'] = 'SELECT category_id, category
	FROM /*_PREFIX_*/contacts
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contacts.contact_category_id = /*_PREFIX_*/categories.category_id
	WHERE categories.parameters LIKE "%&contacts_general=1%"
	ORDER BY category';
