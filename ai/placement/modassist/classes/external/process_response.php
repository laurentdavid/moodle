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
use core_external\restricted_context_exception;

/**
 * External API to call to act on the module once response has been generated.
 *
 * @package    aiplacement_modassist
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_response extends external_api {

    /**
     * Process the returned content to act on the module.
     *
     * @param int $contextid The context ID.
     * @param string $action The action for the module.
     * @param string $generatedcontent
     * @return array The generated content.
     * @throws \moodle_exception
     * @since Moodle 4.5
     */
    public static function execute(
        int $contextid,
        string $action,
        string $generatedcontent
    ): array {
        // Parameter validation.
        [
            'contextid' => $contextid,
            'action' => $action,
            'generatedcontent' => $generatedcontent,
        ] = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'action' => $action,
            'generatedcontent' => $generatedcontent,
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
        try {
            $response = $modinfo->process_response($context, $action, $generatedcontent);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errorcode' => $e->getCode(),
                'error' => $e->getMessage(),
            ];
        }
        // Return the response.
        return [
            'success' => true,
            'message' => $response->get_successmessage() ?? '',
        ];
    }

    /**
     * Summarise text parameters.
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
            'generatedcontent' => new external_value(
                PARAM_RAW,
                'The generated content as text',
                VALUE_REQUIRED,
            ),
        ]);
    }

    /**
     * Generate content return value.
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
            'message' => new external_value(
                PARAM_TEXT,
                'Error message if any',
                VALUE_DEFAULT,
                '',
            ),
        ]);
    }
}
