<?php 

/**
 * contacts module
 * Table with contact verifications
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015, 2018-2021, 2023, 2025-2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Contact Verifications';
$zz['table'] = '/*_PREFIX_*/contacts_verifications';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'cv_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['title'] = 'Contact';
$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['unique'] = true;
$zz['fields'][2]['sql'] = 'SELECT contact_id, contact, identifier
	FROM /*_PREFIX_*/contacts
	ORDER BY identifier';
$zz['fields'][2]['display_field'] = 'contact';
$zz['fields'][2]['list_append_next'] = true;
$zz['fields'][2]['list_suffix'] = '<br>';
$zz['fields'][2]['if']['where']['hide_in_form'] = true;
$zz['fields'][2]['if']['where']['hide_in_list'] = true;
$zz['fields'][2]['if']['where']['list_append_next'] = false;
$zz['fields'][2]['class'] = 'block480a';
$zz['fields'][2]['character_set'] = 'utf8';

$zz['fields'][13]['title'] = 'Confirmed Mail';
$zz['fields'][13]['field_name'] = 'confirmed_mail';
$zz['fields'][13]['type'] = 'write_once';
$zz['fields'][13]['type_detail'] = 'mail';

$zz['fields'][3]['title'] = 'Entry Date';
$zz['fields'][3]['field_name'] = 'entry_date';
$zz['fields'][3]['type'] = 'write_once';
$zz['fields'][3]['type_detail'] = 'datetime';
$zz['fields'][3]['default'] = date('Y-m-d H:i:s');
$zz['fields'][3]['unless']['export_mode']['list_append_next'] = true;

$zz['fields'][5]['title'] = 'Entry IP';
$zz['fields'][5]['field_name'] = 'entry_ip';
$zz['fields'][5]['type'] = 'write_once';
$zz['fields'][5]['type_detail'] = 'ip';
$zz['fields'][5]['default'] = wrap_setting('remote_ip');
$zz['fields'][5]['unless']['export_mode']['list_prefix'] = '<br><small style="color: #999;">';
$zz['fields'][5]['unless']['export_mode']['list_suffix'] = '</small>';
$zz['fields'][5]['unless']['export_mode']['list_append_next'] = true;

$zz['fields'][9]['title'] = 'Hash';
$zz['fields'][9]['field_name'] = 'verification_hash';
$zz['fields'][9]['type'] = 'identifier';
$zz['fields'][9]['class'] = 'hidden';
$zz['fields'][9]['hide_in_list'] = true;
$zz['fields'][9]['identifier']['random_hash'] = 8;
$zz['fields'][9]['fields'] = ['verification_hash'];

$zz['fields'][7]['title'] = 'Verification Date';
$zz['fields'][7]['field_name'] = 'verification_date';
$zz['fields'][7]['type'] = 'hidden';
$zz['fields'][7]['type_detail'] = 'datetime';
$zz['fields'][7]['unless']['export_mode']['list_prefix'] = '<br>';
$zz['fields'][7]['unless']['export_mode']['list_append_show_title'] = true;
$zz['fields'][7]['unless']['export_mode']['list_append_next'] = true;

$zz['fields'][8]['title'] = 'Verification IP';
$zz['fields'][8]['field_name'] = 'verification_ip';
$zz['fields'][8]['type'] = 'hidden';
$zz['fields'][8]['type_detail'] = 'ip';
$zz['fields'][8]['unless']['export_mode']['list_prefix'] = '<br><small style="color: #999;">';
$zz['fields'][8]['unless']['export_mode']['list_suffix'] = '</small>';


$zz['fields'][10]['field_name'] = 'status';
$zz['fields'][10]['type'] = 'select';
$zz['fields'][10]['enum'] = [
	'unverified', 'confirmed per link', 'confirmed manually', 'complete',
	'unsubscribed', 'deleted'
];
$zz['fields'][10]['default'] = 'unverified';

$zz['fields'][11]['field_name'] = 'language_id';
$zz['fields'][11]['type'] = 'write_once';
$zz['fields'][11]['type_detail'] = 'select';
$zz['fields'][11]['default'] = wrap_language_id(wrap_setting('lang'));
$zz['fields'][11]['hide_in_list'] = true;
$zz['fields'][11]['sql'] = 'SELECT language_id, language
	FROM /*_PREFIX_*/languages
	ORDER BY language';
$zz['fields'][11]['sql_translate'] = ['language_id' => 'languages'];
$zz['fields'][11]['display_field'] = 'iso_639_1';
$zz['fields'][11]['exclude_from_search'] = true;

$zz['fields'][12]['field_name'] = 'mails_sent';
$zz['fields'][12]['type'] = 'number';
$zz['fields'][12]['hide_in_list'] = true;
$zz['fields'][12]['default'] = 0;
$zz['fields'][12]['null'] = true;
$zz['fields'][12]['export'] = false;


$zz['sql'] = 'SELECT /*_PREFIX_*/contacts_verifications.*, contact
		, /*_PREFIX_*/languages.iso_639_1
	FROM /*_PREFIX_*/contacts_verifications
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/languages USING (language_id)
';
$zz['sqlorder'] = ' ORDER BY entry_date DESC, contact, identifier';

$zz['subselect']['sql'] = 'SELECT contact_id, verification_date
	FROM /*_PREFIX_*/contacts_verifications';
$zz['export_no_html'] = true;
