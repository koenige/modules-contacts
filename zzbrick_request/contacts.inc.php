<?php 

/**
 * contacts module
 * Contacts list
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_contacts_contacts($params, $settings) {
	if (!$params) {
		$sql = 'SELECT contact_id
			FROM contacts
			LEFT JOIN persons USING (contact_id)
			ORDER BY last_name, first_name, identifier';
	} elseif (count($params) === 1) {
		$sql = 'SELECT contact_id
			FROM contacts
			LEFT JOIN contacts_categories USING (contact_id)
			LEFT JOIN persons USING (contact_id)
			WHERE category_id = /*_ID categories contact-properties/%s _*/
			ORDER BY last_name, first_name, identifier';
		$sql = sprintf($sql, wrap_db_escape($params[0]));
	} else {
		return false;
	}
	$contacts = wrap_db_fetch($sql, 'contact_id');
	
	wrap_include('data', 'zzwrap');
	$contacts = wrap_data('contacts', $contacts);
	
	$first_contact = reset($contacts);
	$data = [
		'templates' => $contacts['templates'] ?? [],
		'scope' => $first_contact['scope'],
		$first_contact['scope'] => true
	];
	unset($contacts['templates']);
	$data['contacts'] = $contacts;

	// $page['title'] = 'Contacts';
	// @todo if category, use category title

	$template = mod_contacts_contacts_template($data);
	$page['text'] = wrap_template($template, $data);
	return $page;
}

/**
 * add template blocks from modules
 *
 * @param array $data
 * @return string
 */
function mod_contacts_contacts_template($data) {
	$tpl = '';
	foreach ($data['templates'] as $block => $templates) {
		$tpl .= '%%% block definition '.$block.' %%%'."\n";
		foreach ($templates as $template) {
			$tpl .= wrap_template($template, [], 'error');
		}
		$tpl .= '%%% block definition end %%%'."\n\n";
	}
	$tpl .= wrap_template('contacts', [], 'error');
	return $tpl;
}
