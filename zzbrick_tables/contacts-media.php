<?php 

/**
 * Zugzwang Project
 * Database table 'Contacts/Media'
 *
 * http://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2014-2015, 2017-2018, 2020 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Contacts/Media';
$zz['table'] = '/*_PREFIX_*/contacts_media';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'contact_medium_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['title'] = 'Contact';
$zz['fields'][2]['field_name'] = 'contact_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT contact_id, contact
	FROM /*_PREFIX_*/contacts
	ORDER BY contact';
$zz['fields'][2]['display_field'] = 'contact';

$zz['fields'][4]['title'] = 'No.';
$zz['fields'][4]['field_name'] = 'sequence';
$zz['fields'][4]['type'] = 'hidden';
$zz['fields'][4]['class'] = 'hidden';
$zz['fields'][4]['value'] = 1;
$zz['fields'][4]['def_val_ignore'] = true;

$zz['fields'][5]['title'] = 'Preview';
$zz['fields'][5]['field_name'] = 'image';
$zz['fields'][5]['type'] = 'image';
$zz['fields'][5]['class'] = 'preview';
$zz['fields'][5]['path'] = [
	'root' => $zz_setting['media_folder'], 
	'webroot' => $zz_setting['files_path'],
	'string1' => '/',
	'field1' => 'filename',
	'string2' => '.',
	'string3' => $zz_setting['media_preview_size'],
	'string4' => '.',
	'extension' => 'thumb_extension',
	'webstring1' => '?v=',
	'webfield1' => 'version'
];

$zz['fields'][3]['title'] = 'Medium';
$zz['fields'][3]['field_name'] = 'medium_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT medium_id
		, CONCAT("[", /*_PREFIX_*/media.medium_id, "] ", /*_PREFIX_*/media.title) AS image
	FROM /*_PREFIX_*/media 
	ORDER BY title';
$zz['fields'][3]['display_field'] = 'image';
$zz['fields'][3]['exclude_from_search'] = true;

$zz['fields'][20]['field_name'] = 'last_update';
$zz['fields'][20]['type'] = 'timestamp';
$zz['fields'][20]['hide_in_list'] = true;

$zz['subselect']['sql'] = 'SELECT contact_id, filename, extension, version
	FROM /*_PREFIX_*/contacts_media
	LEFT JOIN /*_PREFIX_*/media USING (medium_id)
	LEFT JOIN /*_PREFIX_*/filetypes AS t_mime 
		ON /*_PREFIX_*/media.thumb_filetype_id = t_mime.filetype_id
';
$zz['subselect']['concat_fields'] = '';
$zz['subselect']['field_suffix'][0] = '.'.$zz_setting['media_preview_size'].'.';
$zz['subselect']['field_suffix'][1] = '?v=';
$zz['subselect']['prefix'] = '<img src="'.$zz_setting['files_path'].'/';
$zz['subselect']['suffix'] = '">';
$zz['subselect']['dont_mark_search_string'] = true;

$zz['sql'] = 'SELECT /*_PREFIX_*/contacts_media.*
	, contact
	, CONCAT("[", /*_PREFIX_*/media.medium_id, "] ", /*_PREFIX_*/media.title) AS image
	, /*_PREFIX_*/media.filename, /*_PREFIX_*/media.version
	, t_mime.extension AS thumb_extension
	FROM /*_PREFIX_*/contacts_media
	LEFT JOIN /*_PREFIX_*/contacts USING (contact_id)
	LEFT JOIN /*_PREFIX_*/media USING (medium_id)
	LEFT JOIN /*_PREFIX_*/filetypes AS o_mime USING (filetype_id)
	LEFT JOIN /*_PREFIX_*/filetypes AS t_mime 
		ON /*_PREFIX_*/media.thumb_filetype_id = t_mime.filetype_id
	WHERE o_mime.mime_content_type = "image"
';
$zz['sqlorder'] = ' ORDER BY /*_PREFIX_*/contacts.contact DESC, media.sequence';
