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
 * Behat availabilty-related steps definitions.
 *
 * @package    availability_date
 * @category   test
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_availability_date extends behat_base {
    /**
     * Return the list of partial named selectors.
     *
     * @return array
     */
    public static function get_partial_named_selectors(): array {
        return [
            new behat_component_named_selector(
                'Date Restriction', ["//div[contains(h3,concat(%locator%, ': Date restriction'))]"]
            ),
            new behat_component_named_selector(
                'Set Of Restrictions', ["//div[contains(h3,concat(%locator%, ': Set of'))]"]
            ),
        ];
    }

    /**
     * Return a list of the exact named selectors for the component.
     *
     * @return behat_component_named_selector[]
     */
    public static function get_exact_named_selectors(): array {
        return [
            new behat_component_named_selector('Root Restriction',
                ["//div[contains(@class,'availability-field')]/div[contains(@class,'availability-list')]"
                    ."/div[contains(@class,'availability-inner')]/div[contains(@class,'availability-button')]"
                    ."/*[contains(.,%locator%)]"])
        ];
    }
}
