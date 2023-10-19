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
 * Default values.
 *
 * @module     core/local/pagination/events
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export const DEFAULT = {
    ITEMS_PER_PAGE_SINGLE: 12,
    DEFAULT_ITEMS_PER_PAGE: [
        {
            "value": 12,
            "active": true
        },
        {
            "value": 24,
            "active": false
        },
        {
            "value": 48,
            "active": false
        },
        {
            "value": 0,
            "active": false
        }
    ],
    MAX_PAGES: 3,
    DEFAULT_PAGE_CONTEXT: {
        controlplacementbottom: false
    }
};