<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Generator for the fake navtest module.
 *
 * @package   mod_navtest
 * @copyright 2026 Laurent David <laurent.david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_navtest_generator extends testing_module_generator {
    /**
     * Creates an instance of the fake module without relying on add_moduleinfo().
     *
     * @param array|stdClass|null $record
     * @param array|null $options
     * @return stdClass
     */
    public function create_instance($record = null, ?array $options = null) {
        global $DB;

        $this->instancecount++;
        $record = (object) ($record ?? []);
        $options = $options ?? [];

        if (empty($record->course)) {
            throw new coding_exception('module generator requires $record->course');
        }

        if (empty($record->name)) {
            $record->name = 'Navigation test module ' . $this->instancecount;
        }

        $instanceid = $DB->insert_record('navtest', (object) [
            'course' => $record->course,
            'name' => $record->name,
        ]);
        $cmid = $this->precreate_course_module($record->course, $options);

        return $this->post_add_instance($instanceid, $cmid);
    }
}
