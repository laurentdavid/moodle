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

namespace core\router\parameters;

use core\navigation\navigation_anchor_manager;
use core\param;
use core\router\schema\referenced_object;

/**
 * A return URL parameter referenced in the query parameters.
 *
 * @package    core
 * @copyright  Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class query_set_nav_anchor_to_referer extends \core\router\schema\parameters\query_parameter implements referenced_object {
    /**
     * Create a new query_returnurl parameter.
     *
     * @param string $name The name of the parameter to use for the return URL
     * @param mixed ...$extra Additional arguments
     */
    public function __construct(
        string $name = navigation_anchor_manager::NAV_ANCHOR_SET_TO_REFERER_PARAMETER,
        ...$extra,
    ) {
        $extra['name'] = $name;
        $extra['type'] = param::ALPHANUMEXT;

        parent::__construct(...$extra);
    }
}
