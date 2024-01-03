<?php
declare(strict_types=1);

namespace classes\platform\ilias;

use classes\core\security\StackException;
use classes\platform\StackDatabase;
use Exception;
use ilDBInterface;

/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 * This plugin is developed and maintained by SURLABS and is a port of STACK Question for Moodle,
 * originally created by Chris Sangwin.
 *
 * The STACK Question plugin for ILIAS is open-source and licensed under GPL-3.0.
 * For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 * To report bugs or participate in discussions, visit the Mantis system and filter by
 * the category "STACK Question" at https://mantis.ilias.de.
 *
 * More information and source code are available at:
 * https://github.com/surlabs/STACK
 *
 * If you need support, please contact the maintainer of this software at:
 * stack@surlabs.es
 *
 *********************************************************************/
class StackDatabaseIlias extends StackDatabase {
    private ilDBInterface $db;

    public function __construct() {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * Inserts a new row in the database
     *
     * Usage: StackDatabase::insert('table_name', ['column1' => 'value1', 'column2' => 'value2']);
     *
     * @param string $table
     * @param array $data
     * @return void
     * @throws StackException
     */
    public function insertInternal(string $table, array $data): void {
        try {
            $this->db->query("INSERT INTO " . $table . " (" . implode(", ", array_keys($data)) . ") VALUES (" . implode(", ", array_map(function ($value) {
                    return $this->db->quote($value);
            }, array_values($data))) . ")");
        } catch (Exception $e) {
            throw new StackException($e->getMessage());
        }
    }

    /**
     * Inserts a new row in the database, if the row already exists, updates it
     *
     * Usage: StackDatabase::insertOnDuplicatedKey('table_name', ['column1' => 'value1', 'column2' => 'value2']);
     *
     * @param string $table
     * @param array $data
     * @return void
     * @throws StackException
     */
    public function insertOnDuplicatedKeyInternal(string $table, array $data): void {
        try {
            $this->db->query("INSERT INTO " . $table . " (" . implode(", ", array_keys($data)) . ") VALUES (" . implode(", ", array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($data))) . ") ON DUPLICATE KEY UPDATE " . implode(", ", array_map(function ($key, $value) {
                    return $key . " = " . $value;
                }, array_keys($data), array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($data)))));
        } catch (Exception $e) {
            throw new StackException($e->getMessage());
        }
    }

    /**
     * Updates a row/s in the database
     *
     * Usage: StackDatabase::update('table_name', ['column1' => 'value1', 'column2' => 'value2'], ['id' => 1]);
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @return void
     * @throws StackException
     */
    public function updateInternal(string $table, array $data, array $where): void {
        try {
            $this->db->query("UPDATE " . $table . " SET " . implode(", ", array_map(function ($key, $value) {
                    return $key . " = " . $value;
                }, array_keys($data), array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($data)))) . " WHERE " . implode(" AND ", array_map(function ($key, $value) {
                    return $key . " = " . $value;
                }, array_keys($where), array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($where)))));
        } catch (Exception $e) {
            throw new StackException($e->getMessage());
        }
    }

    /**
     * Deletes a row/s in the database
     *
     * Usage: StackDatabase::delete('table_name', ['id' => 1]);
     *
     * @param string $table
     * @param array $where
     * @return void
     * @throws StackException
     */
    public function deleteInternal(string $table, array $where): void {
        try {
            $this->db->query("DELETE FROM " . $table . " WHERE " . implode(" AND ", array_map(function ($key, $value) {
                return $key . " = " . $value;
            }, array_keys($where), array_map(function ($value) {
                return $this->db->quote($value);
            }, array_values($where)))));
        } catch (Exception $e) {
            throw new StackException($e->getMessage());
        }
    }

    /**
     * Selects a row/s in the database
     *
     * Usage: StackDatabase::select('table_name', ['id' => 1]);
     *
     * @param string $table
     * @param array|null $where
     * @param array|null $columns
     * @return array
     * @throws StackException
     */
    public function selectInternal(string $table, ?array $where = null, ?array $columns = null): array {
        try {
            $query = "SELECT " . (isset($columns) ? implode(", ", $columns) : "*") . " FROM " . $table;

            if (isset($where)) {
                $query .= " WHERE " . implode(" AND ", array_map(function ($key, $value) {
                    return $key . " = " . $value;
                }, array_keys($where), array_map(function ($value) {
                    return $this->db->quote($value);
                }, array_values($where))));
            }

            $result = $this->db->query($query);

            $rows = [];

            while ($row = $this->db->fetchAssoc($result)) {
                $rows[] = $row;
            }

            return $rows;
        } catch (Exception $e) {
            throw new StackException($e->getMessage());
        }
    }

    /**
     * Returns the next id for a table
     *
     * Usage: StackDatabase::nextId('table_name');
     *
     * @param string $table
     * @return int
     * @throws StackException
     */
    public function nextIdInternal(string $table) :int {
        try {
            return $this->db->nextId($table);
        } catch (Exception $e) {
            throw new StackException($e->getMessage());
        }
    }
}