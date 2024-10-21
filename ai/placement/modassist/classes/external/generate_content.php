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

namespace aiplacement_modassist\external;

use aiplacement_modassist\utils;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;

/**
 * External API to call summarise text action for this placement.
 *
 * @package    aiplacement_modassist
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generate_content extends external_api {

    /**
     * Generate content for the given action and the given module.
     *
     * @param int $contextid The context ID.
     * @param string $action The action for the module.
     * @param string $data
     * @return array The generated content.
     * @throws \moodle_exception
     * @since Moodle 4.5
     */
    public static function execute(
        int $contextid,
        string $action,
        string $data
    ): array {
        // Parameter validation.
        [
            'contextid' => $contextid,
            'action' => $action,
            'data' => $data,
        ] = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'action' => $action,
            'data' => $data,
        ]);
        // Context validation and permission check.
        // Get the context from the passed in ID.
        $context = \context::instance_by_id($contextid);

        // Check the user has permission to use the AI service.
        self::validate_context($context);
        if (!utils::is_mod_assist_available($context)) {
            throw new \moodle_exception('nomodassist', 'aiplacement_modassist');
        }
        $modinfo = utils::get_info_for_module($context);
        if (empty($modinfo)) {
            throw new \moodle_exception('nomodassist', 'aiplacement_modassist');
        }
        $decodedactiondata = json_decode($data);
        if ($decodedactiondata === null) {
            throw new \moodle_exception('invaliddata', 'aiplacement_modassist');
        }
        $action = $modinfo->get_ai_action($context, $action, $decodedactiondata);
        if ($action === null) {
            throw new \moodle_exception('invalidaction', 'aiplacement_modassist');
        }
        $manager = new \core_ai\manager();
        $response = $manager->process_action($action);
        // Return the response.
        return [
            'success' => $response->get_success(),
            'generatedcontent' => $response->get_response_data()['generatedcontent'] ?? '',
            'finishreason' => $response->get_response_data()['finishreason'] ?? '',
            'errorcode' => $response->get_errorcode(),
            'error' => $response->get_errormessage(),
            'timecreated' => $response->get_timecreated(),
        ];
    }

    /**
     * Generate content for the given action and the given module.
     *
     * @return external_function_parameters
     * @since Moodle 4.5
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'contextid' => new external_value(
                PARAM_INT,
                'The context ID',
                VALUE_REQUIRED,
            ),
            'action' => new external_value(
                PARAM_ALPHANUMEXT,
                'Action for the module',
                VALUE_REQUIRED,
            ),
            'data' => new external_value(
                PARAM_RAW,
                'The data encoded as a json array',
                VALUE_REQUIRED,
            ),
        ]);
    }

    /**
     * Generate content for the given action and the given module return value.
     *
     * @return external_function_parameters
     * @since Moodle 4.5
     */
    public static function execute_returns(): external_function_parameters {
        return new external_function_parameters([
            'success' => new external_value(
                PARAM_BOOL,
                'Was the request successful',
                VALUE_REQUIRED
            ),
            'timecreated' => new external_value(
                PARAM_INT,
                'The time the request was created',
                VALUE_REQUIRED,
            ),
            'generatedcontent' => new external_value(
                PARAM_RAW,
                'The text generated by AI.',
                VALUE_DEFAULT,
            ),
            'finishreason' => new external_value(
                PARAM_ALPHA,
                'The reason generation was stopped',
                VALUE_DEFAULT,
                'stop',
            ),
            'errorcode' => new external_value(
                PARAM_INT,
                'Error code if any',
                VALUE_DEFAULT,
                0,
            ),
            'error' => new external_value(
                PARAM_TEXT,
                'Error message if any',
                VALUE_DEFAULT,
                '',
            ),
        ]);
    }
}
