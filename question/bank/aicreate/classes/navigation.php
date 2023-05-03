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
namespace qbank_aicreate;

/**
 * Class navigation.
 *
 * @package    qbank_aicreate
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navigation extends \core_question\local\bank\navigation_node_base {

    /**
     * Get title
     *
     * @return string
     */
    public function get_navigation_title(): string {
        return get_string('aicreate', 'qbank_aicreate');
    }

    /**
     * Get key
     *
     * @return string
     */
    public function get_navigation_key(): string {
        return 'aicreate';
    }

    /**
     * Get URL
     *
     * @return \moodle_url
     */
    public function get_navigation_url(): \moodle_url {
        return new \moodle_url('/question/bank/aicreate/create.php');
    }

}
