<?php
declare(strict_types=1);

namespace classes\platform;

use classes\platform\StackException;
use classes\platform\ilias\StackDatabaseIlias;

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
abstract class StackDatabase {
    public static StackDatabase $platform;

    /**
     * Sets the platform database (this method is called automatically from StackPlatform::initialize)
     * @param string $x
     * @return void
     * @throws StackException
     * @noinspection PhpSwitchCanBeReplacedWithMatchExpressionInspection
     */
    public static function setPlatform(string $x): void {
        switch ($x) {
            case 'ilias':
                self::$platform = new StackDatabaseIlias();
                break;
            default:
                throw new StackException('Invalid platform selected to DB: ' . $x . '.');
        }
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
    public static function insert(string $table, array $data): void {
        self::$platform->insertInternal($table, $data);
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
    public static function insertOnDuplicatedKey(string $table, array $data): void {
        self::$platform->insertOnDuplicatedKeyInternal($table, $data);
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
    public static function update(string $table, array $data, array $where): void {
        self::$platform->updateInternal($table, $data, $where);
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
    public static function delete(string $table, array $where): void{
        self::$platform->deleteInternal($table, $where);
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
    public static function select(string $table, ?array $where = null, ?array $columns = null): array {
        return self::$platform->selectInternal($table, $where, $columns);
    }

    /**
     * Returns the next id for a table
     *
     * @param string $string
     * @return int
     * @throws StackException
     */
    public static function nextId(string $string) :int {
        return self::$platform->nextIdInternal($string);
    }
}