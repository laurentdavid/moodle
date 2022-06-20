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
namespace tool_policy\external;

use coding_exception;
use context_system;
use dml_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use external_warnings;
use invalid_parameter_exception;
use moodle_exception;
use restricted_context_exception;
use tool_policy\api;

/**
 * Class external.
 *
 * The get_policy_version API for the Policy tool.
 *
 * @package   tool_policy
 * @copyright   2018 Sara Arjona (sara@moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_policy_version extends external_api {

    /**
     * Parameter description for execute_parameters().
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'versionid' => new external_value(PARAM_INT, 'The policy version ID', VALUE_REQUIRED),
            'behalfid' => new external_value(PARAM_INT, 'The id of user on whose behalf the user is viewing the policy',
                VALUE_DEFAULT, 0)
        ]);
    }

    /**
     * Fetch the details of a policy version.
     *
     * @param int $versionid The policy version ID.
     * @param int $behalfid The id of user on whose behalf the user is viewing the policy.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     * @throws moodle_exception
     */
    public static function execute(int $versionid, ?int $behalfid = null): array {
        global $PAGE;

        $result = [];
        $warnings = [];
        $params = external_api::validate_parameters(self::execute_parameters(), [
            'versionid' => $versionid,
            'behalfid' => $behalfid
        ]);
        $versionid = $params['versionid'];
        $behalfid = $params['behalfid'];

        $context = context_system::instance();
        $PAGE->set_context($context);

        try {
            // Validate if the user has access to the policy version.
            $version = api::get_policy_version($versionid);
            if (!api::can_user_view_policy_version($version, $behalfid)) {
                $warnings[] = [
                    'item' => $versionid,
                    'warningcode' => 'errorusercantviewpolicyversion',
                    'message' => get_string('errorusercantviewpolicyversion', 'tool_policy')
                ];
            } else if (!empty($version)) {
                $version = api::get_policy_version($versionid);
                $policy['name'] = $version->name;
                $policy['versionid'] = $versionid;
                list($policy['content'], $notusedformat) = external_format_text(
                    $version->content,
                    $version->contentformat,
                    SYSCONTEXTID,
                    'tool_policy',
                    'policydocumentcontent',
                    $version->id
                );
                $result['policy'] = $policy;
            }
        } catch (moodle_exception $e) {
            $warnings[] = [
                'item' => $versionid,
                'warningcode' => 'errorpolicyversionnotfound',
                'message' => get_string('errorpolicyversionnotfound', 'tool_policy')
            ];
        }

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for execute().
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'result' => new external_single_structure([
                'policy' => new external_single_structure([
                    'name' => new external_value(PARAM_RAW, 'The policy version name', VALUE_OPTIONAL),
                    'versionid' => new external_value(PARAM_INT, 'The policy version id', VALUE_OPTIONAL),
                    'content' => new external_value(PARAM_RAW, 'The policy version content', VALUE_OPTIONAL)
                ], 'Policy information', VALUE_OPTIONAL)
            ]),
            'warnings' => new external_warnings()
        ]);
    }
}
