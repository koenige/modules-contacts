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


$sql = 'SELECT category_id, category, parameters 
	FROM categories
	WHERE main_category_id = %d
	AND parameters LIKE "%%&places=1%%"
	ORDER BY sequence, path';
$sql = sprintf($sql, wrap_category_id('provider'));
$values['contactdetails'] = wrap_db_fetch($sql, 'category_id');

$zz = zzform_include_table('contacts', $values);
$zz['title'] = 'Places';

// contact
$zz['fields'][2]['title'] = 'Place';

// category
$zz['fields'][4]['hide_in_list'] = true;
$zz['fields'][4]['sql'] .= ' AND categories.parameters LIKE "%&event_place=1%"';

// addresses
$zz['fields'][5]['min_records'] = 1;
$zz['fields'][5]['max_records'] = 1;
if (!empty($zz['fields'][5]['fields'][10])) {
	// receive_mail
	$zz['fields'][5]['fields'][10]['type'] = 'hidden';
	$zz['fields'][5]['fields'][10]['type_detail'] = 'enum';
	$zz['fields'][5]['fields'][10]['value'] = 'yes';
	$zz['fields'][5]['fields'][10]['hide_in_form'] = true;
}
$zz['fields'][5]['fields'][9]['sql'] = 'SELECT category_id, category, main_category_id
	FROM categories
	WHERE parameters LIKE "%&places=1%"
	ORDER BY sequence, category';

// published
$zz['fields'][14]['type'] = 'hidden';
$zz['fields'][14]['type_detail'] = 'enum';
$zz['fields'][14]['value'] = 'yes';
$zz['fields'][14]['hide_in_form'] = true;

// parameters
unset($zz['fields'][15]);

$zz['sql'] .= 'WHERE categories.parameters LIKE "%&event_place=1%"';

$zz['filter'][1]['sql'] = 'SELECT category_id, category
	FROM /*_PREFIX_*/contacts
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contacts.contact_category_id = /*_PREFIX_*/categories.category_id
	WHERE categories.parameters LIKE "%&event_place=1%"
	ORDER BY category';
