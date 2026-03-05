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

use core_course\cm_info;

defined('MOODLE_INTERNAL') || die();

/**
 * Feature support callback for the fake module.
 *
 * @param string $feature
 * @return bool|null
 */
function navtest_supports($feature): ?bool {
    return match ($feature) {
        FEATURE_MOD_INTRO => true,
        default => null,
    };
}

/**
 * Add instance callback for the fake module.
 *
 * @param stdClass $data
 * @param mixed $mform
 * @return int
 */
function navtest_add_instance(stdClass $data, $mform = null): int {
    global $DB;
    return (int) $DB->insert_record('navtest', (object) [
        'course' => (int) $data->course,
        'name' => (string) ($data->name ?? ''),
    ]);
}

/**
 * Dynamic module callback forcing no navigation URL.
 *
 * @param cm_info $cm
 */
function mod_navtest_cm_info_dynamic(cm_info $cm): void {
    $cm->set_navigation_url(null);
}
