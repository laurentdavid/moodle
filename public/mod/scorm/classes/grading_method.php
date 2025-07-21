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

namespace mod_scorm;

/**
 * Scorm grading methods enum.
 *
 * @package    mod_scorm
 * @copyright  2025 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
enum grading_method: int {
    case LEARNING_OBJECTS = 0;
    case HIGHEST_GRADE = 1;
    case AVERAGE_GRADE = 2;
    case SUM_GRADE = 3;

    /**
     * Returns the user friendly string representation of the wiki mode.
     *
     * @return string user friendly representation.
     */
    public function to_string(): string {
        $sm = \core\di::get(\core_string_manager::class);
        return match ($this) {
            self::LEARNING_OBJECTS => $sm->get_string('gradescoes', 'mod_scorm'),
            self::HIGHEST_GRADE => $sm->get_string('gradehighest', 'mod_scorm'),
            self::AVERAGE_GRADE => $sm->get_string('gradeaverage', 'mod_scorm'),
            self::SUM_GRADE => $sm->get_string('gradesum', 'mod_scorm'),
        };
    }
}
