<?php 

/**
 * contacts module
 * Table with contact details
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015, 2017-2019, 2021, 2023-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Contact Details';
$zz['table'] = '/*_PREFIX_*/contactdetails';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'contactdetail_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['title'] = 'Contact';
$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	ORDER BY identifier';
$zz['fields'][2]['character_set'] = 'utf8';
$zz['fields'][2]['display_field'] = 'contact';
$zz['fields'][2]['if']['where']['hide_in_form'] = true;
$zz['fields'][2]['if']['where']['hide_in_list'] = true;
$zz['fields'][2]['class'] = 'block480a';

$zz['fields'][3]['field_name'] = 'identification';
$zz['fields'][3]['remove_local_hostname'] = false;

$zz['fields'][5]['field_name'] = 'label';
$zz['fields'][5]['type'] = 'text';
$zz['fields'][5]['hide_in_list'] = true;
$zz['fields'][5]['hide_in_form'] = true;
$zz['fields'][5]['size'] = 6;
if (wrap_setting('contacts_details_with_label'))
	$zz['fields'][5]['hide_in_form'] = false;

$zz['fields'][4]['title'] = 'Type';
$zz['fields'][4]['field_name'] = 'provider_category_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = sprintf('SELECT category_id, category
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d',
	wrap_category_id('provider')
);
$zz['fields'][4]['display_field'] = 'category';
$zz['fields'][4]['character_set'] = 'utf8';

if (in_array('activities', wrap_setting('modules'))) {
	$zz['fields'][6]['field_name'] = 'formfield_id';
	$zz['fields'][6]['type'] = 'select';
	$zz['fields'][6]['sql'] = 'SELECT formfield_id
			, CONCAT(event, " ", formfields.sequence)
		FROM formfields
		LEFT JOIN forms USING (form_id)
		LEFT JOIN events USING (event_id)
		ORDER BY identifier, formfields.sequence';
	$zz['fields'][6]['exclude_from_search'] = true;
	$zz['fields'][6]['hide_in_list'] = true;
	$zz['fields'][6]['hide_in_form'] = true;
}

$zz['fields'][20]['field_name'] = 'last_update';
$zz['fields'][20]['type'] = 'timestamp';
$zz['fields'][20]['hide_in_list'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/contactdetails.*, contact
		, category
	FROM /*_PREFIX_*/contactdetails
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/categories.category_id = /*_PREFIX_*/contactdetails.provider_category_id
';
$zz['sqlorder'] = ' ORDER BY identifier, path, identification';

$zz['unique'][] = ['contact_id', 'identification', 'provider_category_id'];


$zz['subselect']['sql'] = 'SELECT category, identification, contact_id
	FROM /*_PREFIX_*/contactdetails
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contactdetails.provider_category_id = /*_PREFIX_*/categories.category_id';
$zz['if']['export_mode']['subselect']['sql'] = 'SELECT identification, contact_id
	FROM /*_PREFIX_*/contactdetails
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contactdetails.provider_category_id = /*_PREFIX_*/categories.category_id';
$zz['subselect']['concat_fields'] = ' ';
$zz['unless']['export_mode']['subselect']['field_prefix'][0] = '<em>';
$zz['unless']['export_mode']['subselect']['field_suffix'][0] = ':</em>';
$zz['unless']['export_mode']['subselect']['field_suffix'][1] = '<br>';
$zz['unless']['export_mode']['subselect']['prefix'] = '';
$zz['unless']['export_mode']['subselect']['suffix'] = '';
$zz['unless']['export_mode']['subselect']['concat_rows'] = '';
