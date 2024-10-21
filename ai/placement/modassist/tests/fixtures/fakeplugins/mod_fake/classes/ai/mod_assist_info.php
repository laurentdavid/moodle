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

namespace mod_fake\ai;

use aiplacement_modassist\action_process_response;
use context;
use core_ai\aiactions\base as action_base;

/**
 * Class mod_assist_info.
 *
 * @package    aiplacement_modassist
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_assist_info extends \aiplacement_modassist\mod_assist_info {
    /**
     * Get the list of actions that are available for this placement.
     *
     * @return array
     */
    public function get_base_action_list(): array {
        return [
            \core_ai\aiactions\generate_text::class,
        ];
    }

    /**
     * Add the form definitions for the action.
     *
     * @param \MoodleQuickForm $mform
     * @param string $action
     * @return void
     * @throws \coding_exception
     */
    public function add_action_form_definitions(\MoodleQuickForm $mform, string $action): void {
    }


    /**
     * Add the form definitions for the glossary add entry action.
     *
     * @param \MoodleQuickForm $mform
     * @return void
     * @throws \coding_exception
     */
    protected function add_glossary_add_entry_form_definitions(\MoodleQuickForm $mform): void {
    }

    /**
     * Get relevant AI action from parameters
     *
     * @param context $context
     * @param string $action
     * @param object $actiondata
     * @return action_base|null
     */
    public function get_ai_action(context $context, string $action, object $actiondata): ?action_base {
        return null;
    }

    /**
     * Process action response
     *
     * @param context $context
     * @param string $action
     * @param string $generatedcontent
     * @return action_process_response|null $action_process_response
     */
    public function process_response(context $context, string $action, string $generatedcontent): ?action_process_response {
        return null;
    }
}
