<?php 

/**
 * contacts module
 * Table with contacts
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Contacts';
$zz['table'] = '/*_PREFIX_*/contacts';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'contact_id';
$zz['fields'][1]['type'] = 'id';
$zz['fields'][1]['geojson'] = 'id';

$zz['fields'][98] = []; // image

$zz['fields'][2]['title'] = 'Name';
$zz['fields'][2]['field_name'] = 'contact';
$zz['fields'][2]['type'] = 'memo';
$zz['fields'][2]['typo_cleanup'] = true;
$zz['fields'][2]['typo_remove_double_spaces'] = true;
$zz['fields'][2]['trim'] = true;
$zz['fields'][2]['rows'] = 2;
$zz['fields'][2]['cols'] = 50;
$zz['fields'][2]['kml'] = 'title';
$zz['fields'][2]['geojson'] = 'title';
$zz['fields'][2]['export_no_html'] = true;
$zz['fields'][2]['unless']['export_mode']['list_format'] = 'nl2br';
$zz['fields'][2]['unless']['export_mode']['list_suffix'] = '<br>';
$zz['fields'][2]['unless']['export_mode']['list_append_next'] = true;
$zz['fields'][2]['merge_equal'] = true;
$zz['fields'][2]['add_details_destination'] = true;
$zz['fields'][2]['link'] = [
	'function' => 'mf_contacts_profile_path',
	'fields' => ['identifier', 'contact_parameters']
];
$zz['fields'][2]['link_record'] = true;

$zz['fields'][10]['title_tab'] = 'Short';
$zz['fields'][10]['title'] = 'Name, short';
$zz['fields'][10]['field_name'] = 'contact_short';
$zz['fields'][10]['class'] = 'hidden480';
$zz['fields'][10]['hide_in_list'] = true;
if (!wrap_setting('contacts_contact_short'))
	$zz['fields'][10]['hide_in_form'] = true;

$zz['fields'][11]['title'] = 'Name, abbr.';
$zz['fields'][11]['field_name'] = 'contact_abbr';
$zz['fields'][11]['type'] = 'text';
$zz['fields'][11]['hide_in_list'] = true;
if (!wrap_setting('contacts_contact_abbr'))
	$zz['fields'][11]['hide_in_form'] = true;

$zz['fields'][21]['title'] = 'Sort';
$zz['fields'][21]['field_name'] = 'contact_sort';
$zz['fields'][21]['hide_in_list'] = true;
if (!wrap_setting('contacts_contact_sort'))
	$zz['fields'][21]['hide_in_form'] = true;

$zz['fields'][9] = []; // persons

$zz['fields'][3]['field_name'] = 'identifier';
$zz['fields'][3]['type'] = 'identifier';
$zz['fields'][3]['fields'] = ['contact_short', 'contact', 'contact_category_id[parameters]'];
$zz['fields'][3]['identifier']['exists'] = '-';
$zz['fields'][3]['identifier']['ignore_this_if']['contact'] = 'contact_short';
$zz['fields'][3]['identifier']['parameters'] = 'contact_category_id[parameters]';
$zz['fields'][3]['log_username'] = true;
$zz['fields'][3]['hide_in_list'] = true;
$zz['fields'][3]['geojson'] = 'identifier';
$zz['fields'][3]['merge_ignore'] = true;
$zz['fields'][3]['unique'] = true;
$zz['fields'][3]['character_set'] = 'latin1';

$zz['fields'][4]['title'] = 'Category';
$zz['fields'][4]['field_name'] = 'contact_category_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = sprintf('SELECT category_id, category, parameters
	FROM /*_PREFIX_*/categories
	WHERE main_category_id = %d
	ORDER BY sequence, category',
	wrap_category_id('contact')
);
$zz['fields'][4]['sql_ignore'][] = 'parameters';
$zz['fields'][4]['key_field_name'] = 'category_id';
$zz['fields'][4]['if']['where']['hide_in_form'] = true;
$zz['fields'][4]['if']['where']['hide_in_list'] = true;
$zz['fields'][4]['unless']['export_mode']['list_prefix'] = '<em>';
$zz['fields'][4]['unless']['export_mode']['list_suffix'] = '</em>';
$zz['fields'][4]['display_field'] = 'category';
$zz['fields'][4]['geojson'] = 'category';
$contact_categories = wrap_db_fetch($zz['fields'][4]['sql'], 'category_id');
if (count($contact_categories) === 1) $zz['fields'][4]['hide_in_list'] = true;
$zz['fields'][4]['exclude_from_search'] = true;

// addressses
$zz['fields'][5] = [];
$zz['fields'][80] = [];
$zz['fields'][81] = [];
$zz['fields'][82] = [];
$zz['fields'][83] = [];
$zz['fields'][84] = [];
$zz['fields'][85] = [];
$zz['fields'][86] = [];
$zz['fields'][87] = [];
$zz['fields'][88] = [];
$zz['fields'][89] = [];

// contactdetails
$zz['fields'][30] = [];
$zz['fields'][31] = [];
$zz['fields'][32] = [];
$zz['fields'][33] = [];
$zz['fields'][34] = [];
$zz['fields'][35] = [];
$zz['fields'][36] = [];
$zz['fields'][37] = [];
$zz['fields'][38] = [];
$zz['fields'][39] = [];
$zz['fields'][40] = [];
$zz['fields'][41] = [];
$zz['fields'][42] = [];
$zz['fields'][43] = [];
$zz['fields'][44] = [];
$zz['fields'][45] = [];
$zz['fields'][46] = [];
$zz['fields'][47] = [];
$zz['fields'][48] = [];
$zz['fields'][49] = [];

// contacts_verifications
$zz['fields'][7] = [];

$zz['fields'][8]['field_name'] = 'latlon';
$zz['fields'][8]['type'] = 'display';
$zz['fields'][8]['exclude_from_search'] = true;
$zz['fields'][8]['hide_in_form'] = true;
$zz['fields'][8]['unless']['export_mode']['hide_in_list'] = true;
$zz['fields'][8]['geojson'] = 'latitude/longitude';

$zz['fields'][12]['field_name'] = 'description';
$zz['fields'][12]['type'] = 'memo';
$zz['fields'][12]['hide_in_list'] = true;
$zz['fields'][12]['format'] = 'markdown';

// contacts_categories
$zz['fields'][50] = [];
$zz['fields'][51] = [];
$zz['fields'][52] = [];
$zz['fields'][53] = [];
$zz['fields'][54] = [];
$zz['fields'][55] = [];
$zz['fields'][56] = [];
$zz['fields'][57] = [];
$zz['fields'][58] = [];
$zz['fields'][59] = [];

$zz['fields'][16]['title'] = 'Start';
$zz['fields'][16]['title_append'] = 'Period';
$zz['fields'][16]['field_name'] = 'start_date';
$zz['fields'][16]['type'] = 'date';
$zz['fields'][16]['hide_in_list'] = true;
$zz['fields'][16]['append_next'] = true;
if (!wrap_setting('contacts_start_date'))
	$zz['fields'][16]['hide_in_form'] = true;

$zz['fields'][17]['title'] = 'End';
$zz['fields'][17]['field_name'] = 'end_date';
$zz['fields'][17]['type'] = 'date';
$zz['fields'][17]['hide_in_list'] = true;
$zz['fields'][17]['prefix'] = '– ';
if (!wrap_setting('contacts_end_date'))
	$zz['fields'][17]['hide_in_form'] = true;

$zz['fields'][18]['field_name'] = 'country_id';
$zz['fields'][18]['type'] = 'select';
$zz['fields'][18]['sql'] = 'SELECT country_id, country_code, country
	FROM /*_PREFIX_*/countries
	ORDER BY country';
$zz['fields'][18]['sql_translate'] = ['country_id' => 'countries'];
$zz['fields'][18]['sql_character_set'][1] = 'latin1';
$zz['fields'][18]['sql_character_set'][2] = 'latin1';
$zz['fields'][18]['search'] = '/*_PREFIX_*/countries.country';
$zz['fields'][18]['hide_in_list'] = true;
$zz['fields'][18]['hide_in_list_if_empty'] = true;
$zz['fields'][18]['display_field'] = 'country_code';
if (!wrap_setting('contacts_country'))
	$zz['fields'][18]['hide_in_form'] = true;
if (wrap_setting('contacts_country_hierarchical')) {
	$zz['fields'][18]['sql'] = 'SELECT country_id, country_code, country, main_country_id
		FROM /*_PREFIX_*/countries
		ORDER BY country_code3, country';
	$zz['fields'][18]['show_hierarchy'] = 'main_country_id';
}

// relations via contacts-contacts
$zz['fields'][60] = [];
$zz['fields'][61] = [];
$zz['fields'][62] = [];
$zz['fields'][63] = [];
$zz['fields'][64] = [];
$zz['fields'][65] = [];
$zz['fields'][66] = [];
$zz['fields'][67] = [];
$zz['fields'][68] = [];
$zz['fields'][69] = [];

// contacts_identifiers
$zz['fields'][19] = [];

$zz['fields'][13]['field_name'] = 'remarks';
$zz['fields'][13]['type'] = 'memo';
$zz['fields'][13]['format'] = 'markdown';
$zz['fields'][13]['merge_append'] = true;
$zz['fields'][13]['rows'] = 3;
$zz['fields'][13]['hide_in_list'] = true;
$zz['fields'][13]['hide_in_form'] = true;
$zz['fields'][13]['explanation'] = 'Internal remarks';
$zz['fields'][13]['separator_before'] = true;
if (wrap_access('contacts_remarks'))
	$zz['fields'][13]['hide_in_form'] = false;

$zz['fields'][14]['title_tab'] = 'Pub.';
$zz['fields'][14]['field_name'] = 'published';
$zz['fields'][14]['type'] = 'select';
$zz['fields'][14]['enum'] = ['yes', 'no'];
$zz['fields'][14]['default'] = 'yes';
$zz['fields'][14]['class'] = 'hidden480';
$zz['fields'][14]['explanation'] = 'Publish on website?';
if (!wrap_setting('contacts_published')) {
	$zz['fields'][14]['default'] = 'no';
	$zz['fields'][14]['hide_in_form'] = true;
	$zz['fields'][14]['hide_in_list'] = true;
}

$zz['fields'][15]['field_name'] = 'parameters';
$zz['fields'][15]['type'] = 'parameter';
$zz['fields'][15]['hide_in_list'] = true;
if (!wrap_setting('contacts_parameters'))
	$zz['fields'][15]['hide_in_form'] = true;

$zz['fields'][97]['field_name'] = 'created';
$zz['fields'][97]['type'] = 'hidden';
$zz['fields'][97]['type_detail'] = 'datetime';
$zz['fields'][97]['default'] = date('Y-m-d H:i:s');
$zz['fields'][97]['hide_in_list'] = true;
$zz['fields'][97]['merge_ignore'] = true;
$zz['fields'][97]['if']['add']['hide_in_form'] = true;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/contacts.*, category
		, (SELECT CONCAT(latitude, ",", longitude) FROM /*_PREFIX_*/addresses
			WHERE /*_PREFIX_*/addresses.contact_id = /*_PREFIX_*/contacts.contact_id
			LIMIT 1) AS latlon
		, /*_PREFIX_*/categories.parameters AS contact_parameters
		, /*_PREFIX_*/countries.country_code
	FROM /*_PREFIX_*/contacts
	LEFT JOIN /*_PREFIX_*/countries USING (country_id)
	LEFT JOIN /*_PREFIX_*/contacts_verifications USING (contact_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contacts.contact_category_id = /*_PREFIX_*/categories.category_id
';
$zz['sqlorder'] = ' ORDER BY identifier';

$zz['filter'][1]['sql'] = 'SELECT category_id, category
	FROM /*_PREFIX_*/contacts
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/contacts.contact_category_id = /*_PREFIX_*/categories.category_id
	ORDER BY category';
$zz['filter'][1]['title'] = wrap_text('Category');
$zz['filter'][1]['identifier'] = 'category';
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['where'] = 'contact_category_id';
$zz['filter'][1]['field_name'] = 'contact_category_id';

$zz['filter'][2]['title'] = wrap_text('Alpha');
$zz['filter'][2]['identifier'] = 'alpha';
$zz['filter'][2]['type'] = 'list';
$zz['filter'][2]['where'] = 'UPPER(SUBSTRING(/*_PREFIX_*/contacts.contact, 1, 1))';
$zz['filter'][2]['sql'] = 'SELECT DISTINCT 
		UPPER(SUBSTRING(contact, 1, 1)), 
		UPPER(SUBSTRING(contact, 1, 1))
	FROM /*_PREFIX_*/contacts
	ORDER BY UPPER(SUBSTRING(contact, 1, 1))';

if (isset($_GET['nolist']) AND empty($_GET['referer']))
	$zz['page']['dynamic_referer'] = $zz['fields'][2]['link'];

$zz['hooks']['before_insert'][] = 'mf_contacts_hook_check_contactdetails';
$zz['hooks']['before_update'][] = 'mf_contacts_hook_check_contactdetails';

$zz['export'][] = 'CSV Excel';

$zz['record']['redirect_to_referer_zero_records'] = true;

if (wrap_access('contacts_merge'))
	$zz['list']['merge'] = true;
