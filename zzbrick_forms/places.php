<?php 

/**
 * Zugzwang Project
 * Form for places
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz = zzform_include_table('contacts');
$zz['title'] = 'Places';

$zz['sql'] .= 'WHERE categories.parameters LIKE "%&event_place=1%"';

$zz['fields'][4]['sql'] .= ' AND categories.parameters LIKE "%&event_place=1%"';

$zz['filter'][1]['sql'] = 'SELECT category_id, category
	FROM /*_PREFIX_*/contacts
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contacts.contact_category_id = /*_PREFIX_*/categories.category_id
	WHERE categories.parameters LIKE "%&event_place=1%"
	ORDER BY category';
