<?php 

/**
 * Zugzwang Project
 * Table with contact details
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015, 2017-2019 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz_sub['title'] = 'Contact Details';
$zz_sub['table'] = '/*_PREFIX_*/contactdetails';

$zz_sub['fields'][1]['title'] = 'ID';
$zz_sub['fields'][1]['field_name'] = 'contactdetail_id';
$zz_sub['fields'][1]['type'] = 'id';

$zz_sub['fields'][2]['title'] = 'Contact';
$zz_sub['fields'][2]['field_name'] = 'contact_id';
$zz_sub['fields'][2]['type'] = 'select';
$zz_sub['fields'][2]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	ORDER BY identifier';
$zz_sub['fields'][2]['display_field'] = 'contact';
$zz_sub['fields'][2]['if']['where']['hide_in_form'] = true;
$zz_sub['fields'][2]['if']['where']['hide_in_list'] = true;
$zz_sub['fields'][2]['class'] = 'block480a';

$zz_sub['fields'][3]['field_name'] = 'identification';
$zz_sub['fields'][3]['remove_local_hostname'] = false;

$zz_sub['fields'][4]['title'] = 'Type';
$zz_sub['fields'][4]['field_name'] = 'provider_category_id';
$zz_sub['fields'][4]['type'] = 'select';
$zz_sub['fields'][4]['sql'] = sprintf('SELECT category_id, category
	FROM categories
	WHERE main_category_id = %d',
	$zz_setting['category']['provider']
);
$zz_sub['fields'][4]['display_field'] = 'category';
$zz_sub['fields'][4]['character_set'] = 'utf8';

$zz_sub['fields'][20]['field_name'] = 'last_update';
$zz_sub['fields'][20]['type'] = 'timestamp';
$zz_sub['fields'][20]['hide_in_list'] = true;

$zz_sub['sql'] = 'SELECT /*_PREFIX_*/contactdetails.*, contact
		, category
	FROM /*_PREFIX_*/contactdetails
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/categories.category_id = /*_PREFIX_*/contactdetails.provider_category_id
';
$zz_sub['sqlorder'] = ' ORDER BY identifier, path, identification';

$zz_sub['unique'][] = ['contact_id', 'identification', 'provider_category_id'];

