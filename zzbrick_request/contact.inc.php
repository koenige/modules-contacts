<?php 

/**
 * contacts module
 * Contact profile
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021-2024 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_contacts_contact($params, $settings) {
	if (isset($_GET['sendlogin']) AND isset($_POST['sendlogin']))
		return brick_format('%%% make sendlogin '.implode(' ', $params).' %%%');

	$sql = 'SELECT contact_id
		FROM contacts
		WHERE contacts.identifier = "%s"';
	$sql = sprintf($sql, wrap_db_escape(implode('/', $params)));
	$data = wrap_db_fetch($sql, 'contact_id');
	if (!$data) return false;
	if (count($data) !== 1) return false;

	wrap_include_files('zzbrick_request_get/contactdata', 'contacts');
	$data = mod_contacts_get_contactdata($data);
	$data = reset($data);
	$data[$data['scope']] = true;

	// is there a more specific profile page?
	$path = wrap_path('contacts_profile['.$data['scope'].']', $data['identifier']);
	if ($path AND $path !== wrap_setting('request_uri'))
		wrap_redirect($path);
	
	wrap_module_parameters('contacts', $data['parameters']);
	if (!empty($settings['scope'])) {
		if ($settings['scope'] !== $data['scope']) return false;
		switch ($settings['scope']) {
		case 'person':
			$sql = 'SELECT person_id, first_name, name_particle, last_name
					, birth_name, sex, title_prefix, title_suffix
					, date_of_birth, date_of_death, country_id, country
					, IFNULL(
						TIMESTAMPDIFF(YEAR, date_of_birth, IFNULL(CAST(IF(
							SUBSTRING(date_of_death, -6) = "-00-00",
							CONCAT(YEAR(date_of_death), "-01-01"), date_of_death) AS DATE
						), CURDATE())),
						YEAR(IFNULL(date_of_death, CURDATE())) - YEAR(date_of_birth)
					) AS age
					, IF(ISNULL(date_of_death), 1, NULL) AS alive
				FROM persons
				LEFT JOIN countries
					ON persons.nationality_country_id = countries.country_id
				WHERE contact_id = %d';
			$sql = sprintf($sql, $data['contact_id']);
			$data = array_merge($data, wrap_db_fetch($sql));
			if (!empty($data['sex'])) $data[$data['sex']] = true;
			$data = wrap_translate($data, 'countries', 'country_id');
			break;
		case 'organisation':
			break;
		}
	}
	
	if (!empty($data['parents'])) {
		foreach ($data['parents'] as $index => $parents)
			$data['parents'][$index]['relations_path'] = mod_contacts_contact_relations_path($parents, $params[0]);
	}

	$data = mod_contacts_contact_packages($data);
	
	// duplicates?
	$sql = 'SELECT contact_id, identifier
		FROM contacts
		WHERE contact LIKE "%%%s%%"
		AND contact_id != %d';
	$sql = sprintf($sql, wrap_db_escape($data['contact']), wrap_db_escape($data['contact_id']));
	$data['duplicates'] = wrap_db_fetch($sql, 'contact_id');

	// logins
	if ($data['scope'] === 'person'
		AND wrap_setting('login_with_contact_id')
		AND wrap_access('contacts_login')
	) {
		$data['logindata'] = true;
		if (wrap_setting('login_with_login_rights')) {
			$sql = 'SELECT login_id, login_rights
					, FROM_UNIXTIME(last_click) AS last_click
					, IF(logged_in = "yes", IF((last_click + 60 * %d >= UNIX_TIMESTAMP()), 1, NULL), NULL) AS logged_in
					, IF(active = "yes", 1, NULL) as active
				FROM logins
				WHERE contact_id = %d';
		} else {
			$sql = 'SELECT login_id
					, FROM_UNIXTIME(last_click) AS last_click
					, IF(logged_in = "yes", IF((last_click + 60 * %d >= UNIX_TIMESTAMP()), 1, NULL), NULL) AS logged_in
					, IF(active = "yes", 1, NULL) as active
				FROM logins
				WHERE contact_id = %d';
		}
		$sql = sprintf($sql
			, wrap_setting('logout_inactive_after')
			, $data['contact_id']
		);
		$login = wrap_db_fetch($sql);
		if ($login) {
			$data += $login;
			$data['masquerade_link'] = wrap_path('default_masquerade', $data['contact_id']);
		}
	}
	if (count($params) !== 1)
		$data['deep'] = str_repeat('../', count($params) -1);

	if ($data['scope'] === 'person') {
		$page['title'] = trim((!empty($data['title_prefix']) ? $data['title_prefix'].' ' : '')
			.$data['contact']
			.(!empty($data['title_suffix']) ? ' '.$data['title_suffix'] : ''));
	} else {
		$page['title'] = $data['contact'];
	}
	$page['query_strings'] = ['sendlogin'];
	if (isset($_GET['sendlogin'])) $data['sendlogin'] = true;
	$page['breadcrumbs'][]['title'] = $data['contact'];
	$page['dont_show_h1'] = true;
	$template = mod_contacts_contact_template($data);
	$page['text'] = wrap_template($template, $data);
	return $page;
}

/**
 * get further contact data from modules
 *
 * @param array $data
 * @return array
 */
function mod_contacts_contact_packages($data) {
	$files = wrap_include_files('contact');
	if (!$files) {
		$data['templates'] = [];
		return $data;
	}
	foreach (array_keys($files) as $package) {
		wrap_package_activate($package);
		$function = sprintf('mf_%s_contact', $package);
		if (!function_exists($function)) continue;
		$data = $function($data);
	}
	return $data;
}

/**
 * add template blocks from modules
 *
 * @param array $data
 * @return string
 */
function mod_contacts_contact_template($data) {
	$tpl = '';
	foreach ($data['templates'] as $block => $templates) {
		$tpl = '%%% block definition '.$block.' %%%'."\n";
		foreach ($templates as $template) {
			$tpl .= wrap_template($template, [], 'error');
		}
		$tpl .= '%%% block definition end %%%'."\n\n";
	}
	$tpl .= wrap_template('contact', [], 'error');
	return $tpl;
}

/**
 * get relations path per category
 *
 * @param array $relation
 * @param string $identifier
 * @return string
 */
function mod_contacts_contact_relations_path($relation, $identifier) {
	$type = $relation['relation_parameters']['alias'] ?? $relation['relation_path'];
	$type = substr($type, strrpos($type, '/') + 1);
	if (!wrap_setting('contacts_relations_path['.$type.']'))
		wrap_setting_path('contacts_relations_path['.$type.']', 'forms contacts-contacts', ['scope' => $type]);
	if (!wrap_setting('contacts_relations_path['.$type.']')) return '';
	return wrap_setting('base').sprintf(
		wrap_setting('contacts_relations_path['.$type.']'), $identifier
	);
}
