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

namespace mod_glossary\ai\output;

use core\output\renderer_base;

/**
 * Class mod_assist_widget.
 *
 * @package    mod_glossary
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assist_actions implements  \renderable, \templatable {
    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        return [
            'actions' => [
                [
                    'action' => 'glossary-add-entry',
                    'title' => get_string('aiaction:addentries', 'mod_glossary'),
                    'description' => get_string('aiaction:addentries:description', 'mod_glossary'),
                ],
            ],
        ];
    }
}
