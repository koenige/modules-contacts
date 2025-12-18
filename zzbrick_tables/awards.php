<?php 

/**
 * contacts module
 * Table for awards
 *
 * https://www.zugzwang.org/modules/contacts
 * Part of »Zugzwang Project«
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2016-2017, 2019-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Awards';
$zz['table'] = '/*_PREFIX_*/awards';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'award_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['title'] = 'Category';
$zz['fields'][2]['field_name'] = 'award_category_id';
$zz['fields'][2]['type']= 'select';
$zz['fields'][2]['sql'] = 'SELECT category_id, category, main_category_id
	FROM /*_PREFIX_*/categories';
$zz['fields'][2]['display_field'] = 'category';
$zz['fields'][2]['show_hierarchy'] = 'main_category_id';
$zz['fields'][2]['show_hierarchy_subtree'] = wrap_category_id('awards');
$zz['fields'][2]['if'][2]['list_prefix'] = '<del>';
$zz['fields'][2]['if'][2]['list_suffix'] = '</del>';

$zz['fields'][4]['title'] = 'Level';
$zz['fields'][4]['field_name'] = 'award_level';
$zz['fields'][4]['type'] = 'text';
$zz['fields'][4]['maxlength'] = 32;
$zz['fields'][4]['sql'] = 'SELECT DISTINCT award_level, award_level
	FROM /*_PREFIX_*/awards
	WHERE award_level IS NOT NULL
	ORDER BY award_level';
$zz['fields'][4]['explanation'] = 'e.g. Gold, Silver, Bronze';

$zz['fields'][3]['field_name'] = 'contact_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	ORDER BY identifier';
$zz['fields'][3]['display_field'] = 'contact';
$zz['fields'][3]['character_set'] = 'utf8';
$zz['fields'][3]['unless']['export_mode']['list_append_next'] = true;
$zz['fields'][3]['link'] = [
	'function' => 'mf_contacts_profile_path',
	'fields' => ['identifier', 'contact_parameters']
];
$zz['fields'][3]['if'][2]['list_prefix'] = '<del>';

$zz['fields'][5]['title'] = 'Display Name';
$zz['fields'][5]['field_name'] = 'contact_display_name';
$zz['fields'][5]['explanation'] = 'Name at time of award';
$zz['fields'][5]['unless']['export_mode']['list_prefix'] = '<br><em>';
$zz['fields'][5]['unless']['export_mode']['list_suffix'] = '</em>';
$zz['fields'][5]['function'] = 'mf_contacts_display_name';
$zz['fields'][5]['fields'] = ['contact_display_name', 'contact_id'];
$zz['fields'][5]['required'] = false;
$zz['fields'][5]['if']['add']['hide_in_form'] = true;
$zz['fields'][5]['if'][2]['list_suffix'] = '</del>';

$zz['fields'][9] = []; // display a country

$zz['fields'][6]['title_tab'] = 'Date';
$zz['fields'][6]['title'] = 'Date of Award';
$zz['fields'][6]['field_name'] = 'award_date';
$zz['fields'][6]['type'] = 'date';
$zz['fields'][6]['hide_in_list_if_empty'] = true;

$zz['fields'][7]['title'] = 'Duration from';
$zz['fields'][7]['title_tab'] = 'Duration';
$zz['fields'][7]['title_append'] = 'Duration';
$zz['fields'][7]['field_name'] = 'award_year';
$zz['fields'][7]['type'] = 'number';
$zz['fields'][7]['append_next'] = true;
$zz['fields'][7]['list_append_next'] = true;

$zz['fields'][8]['title'] = 'Duration until';
$zz['fields'][8]['field_name'] = 'award_year_to';
$zz['fields'][8]['type'] = 'number';
$zz['fields'][8]['list_prefix'] = '–';
$zz['fields'][8]['prefix'] = '–';

$zz['fields'][10]['field_name'] = 'laudation';
$zz['fields'][10]['type'] = 'memo';
$zz['fields'][10]['format'] = 'markdown';
$zz['fields'][10]['hide_in_list'] = true;

$zz['fields'][11]['field_name'] = 'published';
$zz['fields'][11]['type'] = 'select';
$zz['fields'][11]['enum'] = ['yes', 'no'];
$zz['fields'][11]['default'] = 'yes';
$zz['fields'][11]['hide_in_list'] = true;

$zz['fields'][12]['field_name'] = 'remarks';
$zz['fields'][12]['type'] = 'memo';
$zz['fields'][12]['hide_in_list'] = true;
$zz['fields'][12]['explanation'] = 'Internal remarks';
$zz['fields'][12]['rows'] = 3;

$zz['fields'][99]['field_name'] = 'last_update';
$zz['fields'][99]['type'] = 'timestamp';
$zz['fields'][99]['hide_in_list'] = true;


$zz['sql'] = 'SELECT /*_PREFIX_*/awards.*
		, /*_PREFIX_*/contacts.contact
		, /*_PREFIX_*/contacts.identifier
		, /*_PREFIX_*/categories.category
	FROM /*_PREFIX_*/awards
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/awards.award_category_id = /*_PREFIX_*/categories.category_id
';
$zz['sqlorder'] = ' ORDER BY category, award_year, contacts.identifier';

$zz['filter'][1]['title'] = wrap_text('Category');
$zz['filter'][1]['identifier'] = 'category';
$zz['filter'][1]['sql'] = 'SELECT category_id, category
	FROM /*_PREFIX_*/awards
	LEFT JOIN /*_PREFIX_*/categories
		ON /*_PREFIX_*/awards.award_category_id = /*_PREFIX_*/categories.category_id
	ORDER BY sequence, category';
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['field_name'] = 'award_category_id';
$zz['filter'][1]['where'] = 'award_category_id';

$zz['conditions'][1]['scope'] = 'record';
$zz['conditions'][1]['where'] = 'contacts.contact_category_id = /*_ID categories contact/person _*/';

$zz['conditions'][2]['scope'] = 'record';
$zz['conditions'][2]['where'] = '/*_PREFIX_*/awards.published = "no"';

$zz['record']['copy'] = true;
$zz['export'][] = 'CSV Excel';

