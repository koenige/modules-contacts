<?php 

/**
 * contacts module
 * GDPR info for a person
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/contacts
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_contacts_gdpr($params, $settings) {
	if (count($params) !== 1) return false;
	
	$sql = 'SELECT *
		FROM contacts WHERE identifier = "%s"';
	$sql = sprintf($sql, wrap_db_escape($params[0]));
	$contact = wrap_db_fetch($sql);
	if (!$contact) return false;
	
	$data['contacts'][0] = $contact;
	// get data from tables linked via relations
	$data += mod_contacts_related_data('contacts', $contact['contact_id']);

	// get person data from persons table, if available, for custom queries
	if (!empty($data['persons'][0]['first_name']))
		$contact['first_name'] = $data['persons'][0]['first_name'];
	if (!empty($data['persons'][0]['last_name']))
		$contact['last_name'] = $data['persons'][0]['last_name'];
	if (!empty($data['persons'][0]['name_particle']))
		$contact['name_particle'] = $data['persons'][0]['name_particle'];

	// run custom or module queries
	$data += mod_contacts_gdpr_custom($contact);
	
	// improve readability
	$data = mod_contacts_gdpr_remove_null($data);

	echo wrap_print($data);

	exit;
	
	$page['text'] = wrap_template('gdpr', $data);
	return $page;
}

/**
 * get data from related tables
 *
 * @param string $table
 * @param array $record
 * @return array
 */
function mod_contacts_related_data($table, $record_id) {
	$sql = 'SELECT * FROM _relations
		WHERE master_table = "%s"';
	$sql = sprintf($sql, wrap_db_escape($table));
	$relations = wrap_db_fetch($sql, 'rel_id');
	
	$other = [];
	$data = [];
	foreach ($relations as $relation) {
		$sql = 'SELECT * FROM %s WHERE %s = %d';
		$sql = sprintf($sql
			, $relation['detail_table']
			, $relation['detail_field']
			, $record_id
		);
		$result = wrap_db_fetch($sql, '_dummy_', 'numeric');
		if ($result) {
			$data[$relation['detail_table']] = $result;
			foreach ($data[$relation['detail_table']] as $line) {
				$other[$relation['detail_table']][] = $relation + ['foreign_id' => $line[$relation['detail_id_field']]];
			}
		}
	}
	if ($other) {
		foreach ($other as $other_table => $other_records) {
			foreach ($other_records as $other_record) {
				$other_data = mod_contacts_related_data($other_table, $other_record['foreign_id']);
				if ($other_data) {
					foreach ($other_data as $detail_table => $other_data_per_relation) {
						if (!$other_data_per_relation) continue;
						if (empty($data[$detail_table]))
							$data[$detail_table] = $other_data_per_relation;
						else
							$data[$detail_table] += $other_data_per_relation;
					}
				}
			}
		}
	}
	return $data;
}

/**
 * remove null fields to make data more readable
 *
 * @param array $table
 * @return array
 */
function mod_contacts_gdpr_remove_null($data) {
	foreach ($data as $rel_id => $rel_data) {
		foreach ($rel_data as $index => $line) {
			foreach ($line as $key => $value) {
				if ($value === false) unset ($data[$rel_id][$index][$key]);
				if (is_null($value)) unset ($data[$rel_id][$index][$key]);
			}
		}
	} 
	return $data;
}

/**
 * check custom queries
 *
 * @param array $main_record
 * @return array
 */
function mod_contacts_gdpr_custom($record) {
	$data = [];
	$files = wrap_collect_files('configuration/gdpr.sql', 'modules/custom');
	foreach ($files as $file) {
		$queries = wrap_sql_file($file);
		foreach ($queries as $table => $table_queries) {
			foreach ($table_queries as $sql) {
				$sql = mod_contacts_gdpr_sql_replace_field($sql, $record);
				$result = wrap_db_fetch($sql, '_dummy_', 'numeric');
				if (!$result) continue;
				if (empty($data[$table])) $data[$table] = $result;
				else $data[$table] += $result;
			}
		}
	}
	return $data;
}

/**
 * replace placeholders in custom queries
 *
 * @param array $main_record
 * @return array
 */
function mod_contacts_gdpr_sql_replace_field($sql, $record) {
	if (!strstr($sql, '/*_FIELD')) return $sql;
	mod_contacts_gdpr_sql_replace_callback($record);
	$sql = preg_replace_callback('~/\*_FIELD ([a-z_]+) _\*/~', 'mod_contacts_gdpr_sql_replace_callback', $sql);
	return $sql;
}

/**
 * callback function for placeholders in SQL queries
 *
 * @param array $data
 * @return array
 */
function mod_contacts_gdpr_sql_replace_callback($data) {
	static $record = [];

	// a) set data
	if (empty($data[0])) $record = $data;
	if (empty($data[1])) return '';

	// b) replace data
	if (!empty($record[$data[1]])) return $record[$data[1]];
	return '';
}
