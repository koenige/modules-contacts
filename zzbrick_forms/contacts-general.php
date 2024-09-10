<?php 

/**
 * contacts module
 * Form for contacts without persons
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021, 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$values = [];
$values['relations_restrict_to'] = 'contacts';

$zz = zzform_include('contacts', $values, 'forms');
$zz['title'] = 'Contacts (without persons)';

$zz['sql'] .= 'WHERE categories.parameters LIKE "%&contacts_general=1%"';

$zz['fields'][4]['sql'] .= ' AND categories.parameters LIKE "%&contacts_general=1%"';

$zz['filter'][1]['sql'] = wrap_edit_sql($zz['filter'][1]['sql'],
	'WHERE', 'categories.parameters LIKE "%&contacts_general=1%"'
);

$zz['filter'][2]['sql'] = wrap_edit_sql($zz['filter'][2]['sql'],
	'JOIN', 'LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contacts.contact_category_id = /*_PREFIX_*/categories.category_id'
);
$zz['filter'][2]['sql'] = wrap_edit_sql($zz['filter'][2]['sql'],
	'WHERE', 'categories.parameters LIKE "%&contacts_general=1%"'
);

$zz['filter'][3]['title'] = wrap_text('Active');
$zz['filter'][3]['identifier'] = 'active';
$zz['filter'][3]['type'] = 'list';
$zz['filter'][3]['where'] = 'contacts.end_date';
$zz['filter'][3]['selection']['NULL'] = wrap_text('yes');
$zz['filter'][3]['selection']['!NULL'] = wrap_text('no');
