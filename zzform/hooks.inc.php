<?php 

/**
 * contacts module
 * hook functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021, 2023-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */

/**
 * check if data looks like SPAM, send back to user to confirm
 *
 * @return array
 */
function mf_contacts_check() {
	global $zz_conf;

	// just do this check once
	$token = sha1($zz_conf['int']['secret_key'].$_POST['zz_id']);
	if (!empty($_POST['zz_token']) AND $_POST['zz_token'] === $token) {
		if (!empty($_POST['zz_identical'])) return [];
		if (!empty($_POST['zz_mixedcase'])) return [];
	}
	$change = [];
	$change['output_form'] = sprintf('<input type="hidden" name="zz_token" value="%s">', $token);
	return $change;	
}

/**
 * check if contact looks like SPAM, send back to user to confirm
 *
 * @param array $ops
 * @return array
 */
function mf_contacts_check_contact($ops) {
	$change = mf_contacts_check();
	if (!$change) return [];

	foreach ($ops['planned'] as $index => $table) {
		if ($table['table'] !== 'contacts') continue;
		if (mf_contacts_check_mixedcase($ops['record_new'][$index]['contact'])) {
			$change['no_validation'] = true;
			$change['valdidation_msg'] = 'Please check the name you entered.';
			$change['validation_fields'][$index] = [
				'contact' => [
					'class' => 'reselect',
					'explanation' => wrap_template('contact-namecheck', ['mixedcase' => true])
				]
			];
		}
	}
	return $change;
}


/**
 * check if first and last name are equal or names look like SPAM,
 * send back to user to confirm
 *
 * @param array $ops
 * @return array
 */
function mf_contacts_check_names($ops) {
	$change = mf_contacts_check();
	if (!$change) return [];

	foreach ($ops['planned'] as $index => $table) {
		if ($table['table'] !== 'persons') continue;
		if ($ops['record_new'][$index]['first_name'] === $ops['record_new'][$index]['last_name']) {
			$change['no_validation'] = true;
			$change['valdidation_msg'] = 'Please check the name you entered.';
			$change['validation_fields'][$index] = [
				'first_name' => [
					'class' => 'reselect'
				],
				'last_name' => [
					'class' => 'reselect',
					'explanation' => wrap_template('contact-namecheck', ['identical' => true])
				]
			];
		}
		if (mf_contacts_check_mixedcase($ops['record_new'][$index]['first_name'])
			OR mf_contacts_check_mixedcase($ops['record_new'][$index]['last_name'])) {
			$change['no_validation'] = true;
			$change['valdidation_msg'] = 'Please check the name you entered.';
			$change['validation_fields'][$index] = [
				'first_name' => [
					'class' => 'reselect'
				],
				'last_name' => [
					'class' => 'reselect',
					'explanation' => wrap_template('contact-namecheck', ['mixedcase' => true])
				]
			];
		}
	}
	return $change;
}

/**
 * check if a name is made up of both lower and uppercase letters after first letter
 * which can occur for human people but most of the time it is SPAM
 *
 * @param string $name
 * @return bool
 */
function mf_contacts_check_mixedcase($name) {
	// this is not a complete test, just against spammers who use ASCII characters only
	$name = trim($name);
	if (mb_strlen($name) === 1) return false;
	if (!preg_match('/^[a-zA-Z]+$/', $name)) return false;
	$name = mb_substr($name, 1);
	if (ctype_lower($name)) return false;
	preg_match_all('/[A-Z]/', $name, $matches);
	if (count($matches[0]) === 1) return false; // McSomething etc.
	return true;
}

/**
 * if a provider category only allows a certain number of records, move more records
 * to a different provider category (e. g. mails to extra-mails or so)
 * via parameters `move_more_records_to`, `max_records`
 *
 * @param array $ops
 * @return array
 */
function mf_contacts_hook_check_contactdetails($ops) {
	$record_count = [];
	foreach ($ops['planned'] as $index => $table) {
		if ($table['table'] !== 'contactdetails') continue;
		if (empty($ops['record_new'][$index])) continue; // deleted
		$provider_category_id = $ops['record_new'][$index]['provider_category_id'];
		if (!array_key_exists($provider_category_id, $record_count))
			$record_count[$provider_category_id] = 1;
		else
			$record_count[$provider_category_id] ++;
	}
	$category_ids = [];
	foreach ($record_count as $category_id => $count) {
		if ($count === 1) continue;
		if (!$category_id) continue; // deleted record, still there, but incomplete
		$category_ids[] = $category_id;
	}
	if (!$category_ids) return [];

	$change = [];
	$sql = 'SELECT category_id, parameters
		FROM /*_PREFIX_*/categories
		WHERE category_id IN (%s)';
	$sql = sprintf($sql, implode(',', $category_ids));
	$categories = wrap_db_fetch($sql, 'category_id');
	foreach ($categories as $category_id => $category) {
		if (!$category['parameters']) continue;
		parse_str($category['parameters'], $parameters);
		if (empty($parameters['move_more_records_to'])) continue;
		if (empty($parameters['max_records'])) continue;
		if ($record_count[$category_id] <= $parameters['max_records']) continue;
		$shown_record_count = 0;
		foreach ($ops['planned'] as $index => $table) {
			if ($table['table'] !== 'contactdetails') continue;
			if ($ops['record_new'][$index]['provider_category_id'].'' !== $category_id.'') continue;
			$shown_record_count++;
			if ($shown_record_count <= $parameters['max_records']) continue;
			$change['record_replace'][$index]['provider_category_id'] = wrap_category_id($parameters['move_more_records_to']);
		}
	}
	return $change;
}
