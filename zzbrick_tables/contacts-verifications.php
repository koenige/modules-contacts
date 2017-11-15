<?php 

/**
 * Zugzwang Project
 * Table with contact verifications
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2015 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz_sub['title'] = 'Contact Verification';
$zz_sub['table'] = '/*_PREFIX_*/contacts_verifications';

$zz_sub['fields'][1]['title'] = 'ID';
$zz_sub['fields'][1]['field_name'] = 'cv_id';
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

$zz_sub['fields'][3]['title'] = 'Entry Date';
$zz_sub['fields'][3]['field_name'] = 'entry_date';
$zz_sub['fields'][3]['type'] = 'hidden';
$zz_sub['fields'][3]['type_detail'] = 'datetime';
$zz_sub['fields'][3]['default'] = date('Y-m-d H:i:s');
$zz_sub['fields'][3]['unless']['export_mode']['list_append_next'] = true;

$zz_sub['fields'][5]['title'] = 'Entry IP';
$zz_sub['fields'][5]['field_name'] = 'entry_ip';
$zz_sub['fields'][5]['type'] = 'hidden';
$zz_sub['fields'][5]['type_detail'] = 'ip';
$zz_sub['fields'][5]['default'] = $_SERVER['REMOTE_ADDR'];
$zz_sub['fields'][5]['unless']['export_mode']['list_prefix'] = '<br><small style="color: #999;">';
$zz_sub['fields'][5]['unless']['export_mode']['list_suffix'] = '</small>';
$zz_sub['fields'][5]['unless']['export_mode']['list_append_next'] = true;

$zz_sub['fields'][9]['title'] = 'Hash';
$zz_sub['fields'][9]['field_name'] = 'verification_hash';
$zz_sub['fields'][9]['type'] = 'hidden';
$zz_sub['fields'][9]['class'] = 'hidden';
$zz_sub['fields'][9]['hide_in_list'] = true;
$zz_sub['fields'][9]['function'] = 'mod_newsletters_random_hash';
$zz_sub['fields'][9]['fields'] = array('verification_hash');

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

$zz_sub['sql'] = 'SELECT /*_PREFIX_*/contacts_verifications.*, contact
	FROM /*_PREFIX_*/contacts_verifications
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
';
$zz_sub['sqlorder'] = ' ORDER BY entry_date DESC, contact, identifier';
