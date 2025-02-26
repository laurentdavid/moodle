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
 * Fix availability condition since we removed the COMPLETION_COMPLETE_FAIL condition.
 *
 * @package   mod_scorm
 * @copyright 2025 Laurent David <laurent.david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_availability\info;
use core_availability\info_module;
use core_availability\info_section;

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
global $CFG, $DB;
require_once($CFG->libdir . '/clilib.php');

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'courseid' => 0,
    ],
    [
        'h' => 'help',
        'c' => 'courseid',
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
        "Fix availability condition since we removed the COMPLETION_COMPLETE_FAIL condition.

        php fix_availability.php --courseid=5
        php fix_availability.php

Options:
--courseid=5            Course ID
-h, --help              Print out this help
";
    cli_error($help);
}

$courseid = $options['courseid'];
if ($courseid) {
    $params = ['courseid' => $courseid];
} else {
    $params = [];
}
$courses = $DB->get_recordset('course', []);

/**
 * Fix availability condition.
 *
 * @param stdClass $availability Availability condition
 * @param course_modinfo $modinfo Course module info.
 * @return object|null
 */
function fix_availability(stdClass $availability, course_modinfo $modinfo, info $availabiltiyinfo): ?object {
    $haschanged = false;

    if (isset($availability->op)) {
        $conditions = &$availability->c;
        foreach ($conditions as $condition) {
            if ($condition->type === 'completion') {
                if (!isset($condition->e) || $condition->e !== COMPLETION_COMPLETE_FAIL) {
                    continue;
                }
                $conditionobj = new \availability_completion\condition($condition);
                list($selfcmid, $selfsectionid) = $conditionobj->get_selfids($availabiltiyinfo);
                // It could be either the cmid in the availability condition or -1 meaning previous activity.
                $cmid = $conditionobj->get_cmid($modinfo->get_course(), $selfcmid, $selfsectionid);
                $mod = $modinfo->get_cm($cmid);

                if ($mod->modname !== 'scorm') {
                    continue;
                }
                $condition->e = COMPLETION_COMPLETE;
                $haschanged = true;
            }
        }
    }
    if ($haschanged) {
        return $availability;
    }
    return null;
}

foreach ($courses as $course) {
    $modinfo = get_fast_modinfo($course->id);
    $mods = $modinfo->get_cms();
    foreach ($mods as $cm) {
        if ($cm->availability) {
            $info = new info_module($cm);
            $availabilityinfo = json_decode($cm->availability);
            $newavailability = fix_availability($availabilityinfo, $modinfo, $info);
            if ($newavailability) {
                $DB->set_field('course_modules', 'availability', json_encode($newavailability), ['id' => $cm->id]);
                cli_writeln("Availability condition fixed for
                        {$cm->get_name()} ({$cm->id}) in course {$course->fullname} ({$course->id})");
            }
        }
    }
    $sections = $modinfo->get_section_info_all();
    foreach ($sections as $section) {
        if ($section->availability) {
            $info = new info_section($section);
            $availabilityinfo = json_decode($section->availability);
            $newavailability = fix_availability($availabilityinfo, $modinfo, $info);
            if ($newavailability) {
                $DB->set_field('course_sections', 'availability', json_encode($newavailability), ['id' => $section->id]);
                cli_writeln("Availability condition fixed for
                        section {$section->name} ({$section->section}) in course {$course->fullname} ({$course->id})");
            }
        }
    }
}
purge_all_caches(); // Important so we can see the changes.
$courses->close();
