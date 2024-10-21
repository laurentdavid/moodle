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

namespace aiplacement_modassist;

use context;
use core_ai\aiactions\base as action_base;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Class mod_assist_info.
 *
 * @package    aiplacement_modassist
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class mod_assist_info {
    /**
     * Constructor.
     *
     * @param \context $context The context.
     */
    public function __construct(
        /** @var \context $context the current context */
        protected \context $context
    ) {
    }

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
     * Get the list of actions that are available for this placement.
     *
     * @param \MoodleQuickForm $mform
     * @param string $action
     * @return void
     */
    abstract public function add_action_form_definitions(\MoodleQuickForm $mform, string $action): void;

    /**
     * Get relevant AI action from parameters
     *
     * @param context $context
     * @param string $action
     * @param object $actiondata
     * @return action_base|null
     */
    abstract public function get_ai_action(context $context, string $action, object $actiondata): ?action_base;

    /**
     * Process action response
     *
     * @param context $context
     * @param string $action
     * @param string $generatedcontent
     * @return action_process_response|null $action_process_response
     */
    abstract public function process_response(context $context, string $action, string $generatedcontent): ?action_process_response;

}
