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

use context_system;
use external_api;
use external_function_parameters;
use external_value;
use tool_policy\form\accept_policy;

/**
 * Class external.
 *
 * The submit_accept_on_behalf API for the Policy tool.
 *
 * @copyright   2018 Sara Arjona (sara@moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submit_accept_on_behalf extends external_api {
    /**
     * Describes the parameters for submit_create_group_form webservice.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            array(
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array')
            )
        );
    }

    /**
     * Submit the create group form.
     *
     * @param string $jsonformdata The data from the form, encoded as a json array.
     * @return int new group id.
     */
    public static function execute(string $jsonformdata): int {
        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::execute_parameters(),
            ['jsonformdata' => $jsonformdata]);

        self::validate_context(context_system::instance());

        $serialiseddata = json_decode($params['jsonformdata']);

        $data = array();
        parse_str($serialiseddata, $data);

        // The last param is the ajax submitted data.
        $mform = new accept_policy(null, $data, 'post', '', null, true, $data);

        // Do the action.
        $mform->process();

        return true;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value
     * @since Moodle 3.0
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_BOOL, 'success');
    }
}
