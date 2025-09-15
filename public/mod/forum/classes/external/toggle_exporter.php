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

namespace mod_forum\external;

use core\external\exporter;
use mod_forum\output\courseformat\toggle;

/**
 * Class to export toggle data for external use.
 *
 * @package    mod_forum
 * @copyright  2025 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class toggle_exporter extends exporter {
    /**
     * Constructor with typed params.
     *
     * @param toggle $data The toggle data to export.
     * @param array $related Related data for the exporter.
     */
    public function __construct(
        toggle $data,
        array $related = [],
    ) {
        parent::__construct($data, $related);
    }

    #[\Override]
    protected static function define_properties(): array {
        return [];
    }

    #[\Override]
    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    #[\Override]
    protected static function define_other_properties() {
        return [
            'type' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
                'description' => 'The type of toggle.',
            ],
            'checked' => [
                'type' => PARAM_BOOL,
                'null' => NULL_NOT_ALLOWED,
                'description' => 'Wether the toggle is checked.',
            ],
            'disabled' => [
                'type' => PARAM_BOOL,
                'null' => NULL_NOT_ALLOWED,
                'description' => 'Whether the dialog is disabled or not.',
                'default' => false,
            ],
            'label' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
                'description' => 'The label of toggle.',
            ],
        ];
    }
}
