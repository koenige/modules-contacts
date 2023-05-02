<?php 

/**
 * contacts module
 * Form for association of contacts to other contacts
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021, 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (count($brick['vars']) !== 1) wrap_quit(404);

$sql = 'SELECT contact_id, contact
	FROM contacts
	WHERE identifier = "%s"';
$sql = sprintf($sql, wrap_db_escape($brick['vars'][0]));
$contact = wrap_db_fetch($sql);
if (!$contact) wrap_quit(404);

if (!empty($brick['local_settings']['scope'])) {
	$sql = 'SELECT category_id, category, parameters
		FROM categories
		WHERE category_id = %d';
	$sql = sprintf($sql
		, wrap_category_id('relation/'.$brick['local_settings']['scope'])
	);
	$category = wrap_db_fetch($sql);
	parse_str($category['parameters'], $category['parameters']);
	if (!$category) wrap_quit(404);
} else {
	$category = [];
}

$zz = zzform_include('contacts-contacts');
$zz['where']['main_contact_id'] = $contact['contact_id'];
if ($category)
	$zz['where']['relation_category_id'] = $category['category_id'];

if (!$category)
	$zz['title'] = $contact['contact'];
else
	$zz['title'] = sprintf('<a href="../">%s</a>:<br>%s'
		, $contact['contact']
		, !empty($category['parameters']['parents']['relation']) ? $category['parameters']['parents']['relation'] : $category['category']
	);

if (!empty($category['parameters']['contact']['add_details'])) {
	$zz['fields'][2]['add_details'] = $category['parameters']['contact']['add_details'];
}
if (!empty($category['parameters']['contact']['category'])) {
	$zz['fields'][2]['sql'] = wrap_edit_sql($zz['fields'][2]['sql'], 'WHERE',
		sprintf('contact_category_id = %d', wrap_category_id('contact/'.$category['parameters']['contact']['category']))
	);
}

$zz['fields'][6]['type'] = 'sequence';

$zz['fields'][11]['size'] = 32;

$zz_conf['referer'] = '../';
