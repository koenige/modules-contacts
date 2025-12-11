<?php 

/**
 * contacts module
 * Table with contact verifications
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015, 2018-2021, 2023, 2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz_sub['title'] = 'Contact Verifications';
$zz_sub['table'] = '/*_PREFIX_*/contacts_verifications';

$zz_sub['fields'][1]['title'] = 'ID';
$zz_sub['fields'][1]['field_name'] = 'cv_id';
$zz_sub['fields'][1]['type'] = 'id';

$zz_sub['fields'][2]['title'] = 'Contact';
$zz_sub['fields'][2]['field_name'] = 'contact_id';
$zz_sub['fields'][2]['type'] = 'select';
$zz_sub['fields'][2]['unique'] = true;
$zz_sub['fields'][2]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	ORDER BY identifier';
$zz_sub['fields'][2]['display_field'] = 'contact';
$zz_sub['fields'][2]['list_append_next'] = true;
$zz_sub['fields'][2]['list_suffix'] = '<br>';
$zz_sub['fields'][2]['if']['where']['hide_in_form'] = true;
$zz_sub['fields'][2]['if']['where']['hide_in_list'] = true;
$zz_sub['fields'][2]['if']['where']['list_append_next'] = false;
$zz_sub['fields'][2]['class'] = 'block480a';
$zz_sub['fields'][2]['character_set'] = 'utf8';

$zz_sub['fields'][13]['title'] = 'Confirmed Mail';
$zz_sub['fields'][13]['field_name'] = 'confirmed_mail';
$zz_sub['fields'][13]['type'] = 'write_once';
$zz_sub['fields'][13]['type_detail'] = 'mail';

$zz_sub['fields'][3]['title'] = 'Entry Date';
$zz_sub['fields'][3]['field_name'] = 'entry_date';
$zz_sub['fields'][3]['type'] = 'write_once';
$zz_sub['fields'][3]['type_detail'] = 'datetime';
$zz_sub['fields'][3]['default'] = date('Y-m-d H:i:s');
$zz_sub['fields'][3]['unless']['export_mode']['list_append_next'] = true;

$zz_sub['fields'][5]['title'] = 'Entry IP';
$zz_sub['fields'][5]['field_name'] = 'entry_ip';
$zz_sub['fields'][5]['type'] = 'write_once';
$zz_sub['fields'][5]['type_detail'] = 'ip';
$zz_sub['fields'][5]['default'] = wrap_setting('remote_ip');
$zz_sub['fields'][5]['unless']['export_mode']['list_prefix'] = '<br><small style="color: #999;">';
$zz_sub['fields'][5]['unless']['export_mode']['list_suffix'] = '</small>';
$zz_sub['fields'][5]['unless']['export_mode']['list_append_next'] = true;

$zz_sub['fields'][9]['title'] = 'Hash';
$zz_sub['fields'][9]['field_name'] = 'verification_hash';
$zz_sub['fields'][9]['type'] = 'identifier';
$zz_sub['fields'][9]['class'] = 'hidden';
$zz_sub['fields'][9]['hide_in_list'] = true;
$zz_sub['fields'][9]['identifier']['random_hash'] = 8;
$zz_sub['fields'][9]['fields'] = ['verification_hash'];

$zz_sub['fields'][7]['title'] = 'Verification Date';
$zz_sub['fields'][7]['field_name'] = 'verification_date';
$zz_sub['fields'][7]['type'] = 'hidden';
$zz_sub['fields'][7]['type_detail'] = 'datetime';
$zz_sub['fields'][7]['unless']['export_mode']['list_prefix'] = '<br>';
$zz_sub['fields'][7]['unless']['export_mode']['list_append_show_title'] = true;
$zz_sub['fields'][7]['unless']['export_mode']['list_append_next'] = true;

$zz_sub['fields'][8]['title'] = 'Verification IP';
$zz_sub['fields'][8]['field_name'] = 'verification_ip';
$zz_sub['fields'][8]['type'] = 'hidden';
$zz_sub['fields'][8]['type_detail'] = 'ip';
$zz_sub['fields'][8]['unless']['export_mode']['list_prefix'] = '<br><small style="color: #999;">';
$zz_sub['fields'][8]['unless']['export_mode']['list_suffix'] = '</small>';


$zz_sub['fields'][10]['field_name'] = 'status';
$zz_sub['fields'][10]['type'] = 'select';
$zz_sub['fields'][10]['enum'] = [
	'unverified', 'confirmed per link', 'confirmed manually', 'complete',
	'unsubscribed', 'deleted'
];
$zz_sub['fields'][10]['default'] = 'unverified';

$zz_sub['fields'][11]['field_name'] = 'language_id';
$zz_sub['fields'][11]['type'] = 'write_once';
$zz_sub['fields'][11]['type_detail'] = 'select';
$zz_sub['fields'][11]['default'] = wrap_language_id(wrap_setting('lang'));
$zz_sub['fields'][11]['hide_in_list'] = true;
$zz_sub['fields'][11]['sql'] = 'SELECT language_id, language
	FROM /*_PREFIX_*/languages
	ORDER BY language';
$zz_sub['fields'][11]['sql_translate'] = ['language_id' => 'languages'];
$zz_sub['fields'][11]['display_field'] = 'iso_639_1';
$zz_sub['fields'][11]['exclude_from_search'] = true;

$zz_sub['fields'][12]['field_name'] = 'mails_sent';
$zz_sub['fields'][12]['type'] = 'number';
$zz_sub['fields'][12]['hide_in_list'] = true;
$zz_sub['fields'][12]['default'] = 0;
$zz_sub['fields'][12]['null'] = true;
$zz_sub['fields'][12]['export'] = false;


$zz_sub['sql'] = 'SELECT /*_PREFIX_*/contacts_verifications.*, contact
		, /*_PREFIX_*/languages.iso_639_1
	FROM /*_PREFIX_*/contacts_verifications
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/languages USING (language_id)
';
$zz_sub['sqlorder'] = ' ORDER BY entry_date DESC, contact, identifier';

$zz_sub['subselect']['sql'] = 'SELECT contact_id, verification_date
	FROM /*_PREFIX_*/contacts_verifications';
$zz_sub['export_no_html'] = true;
