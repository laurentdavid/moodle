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
 * Contains the default activity control menu.
 *
 * @package   core_courseformat
 * @copyright 2024 Laurent David <laurent.david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_social\output\courseformat\content\cm;

use core_courseformat\output\local\content\cm\controlmenu as genericontrolmenu;

/**
 * Base class to render a course module menu inside a course format.
 *
 * @package   core_courseformat
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controlmenu extends genericontrolmenu {
    /**
     * Generate the control items for the activity.
     *
     * For social: the move action dialog is not available.
     *
     * @return array of control items
     */
    protected function cm_control_items() {
        $cmitems = parent::cm_control_items();
        unset($cmitems['move']);
        return $cmitems;
    }
}
