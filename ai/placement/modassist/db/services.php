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
 * Course Assistance Placement webservice definitions.
 *
 * @package    aiplacement_modassist
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'aiplacement_modassist_process_response' => [
        'classname' => 'aiplacement_modassist\external\process_response',
        'description' => 'Process response from a Module Action Assistance Placement',
        'type' => 'write',
        'ajax' => true,
    ],
    'aiplacement_modassist_generate_content' => [
        'classname' => 'aiplacement_modassist\external\generate_content',
        'description' => 'Generate content for Module Action Assistance Placement to be processes by process_action later',
        'type' => 'write',
        'ajax' => true,
    ],
];
