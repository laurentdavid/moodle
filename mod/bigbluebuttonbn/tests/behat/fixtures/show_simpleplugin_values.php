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
 * Index page to display information about the plugin and its instances values.
 */

use mod_bigbluebuttonbn\instance;

require(__DIR__ . '/../../../../../config.php');

global $PAGE, $OUTPUT, $DB;
defined('BEHAT_SITE_RUNNING') || die();
$PAGE->set_url('/mod/bigbluebuttonbn/tests/behat/fixtures/show_simpleplugin_values.php');
$PAGE->set_context(context_system::instance());
$instances = [];
foreach ($DB->get_records('bigbluebuttonbn') as $key => $value) {
    $instances[] = instance::get_from_instanceid($value->id);
}
$OUTPUT->header();
echo $OUTPUT->heading('Instance information');
echo $OUTPUT->box_start();
echo 'This page displays information about the plugin and its instances values.';
foreach ($instances as $instance) {
    $name = $instance->get_meeting_name();
    echo "Instance ID ($name): {$instance->get_instance_id()}<br>";
    echo "Meeting ID ($name): {$instance->get_meeting_id()}<br>";
    echo "Meeting Description ($name): {$instance->get_meeting_description()}<br>";
    $simplepluginvalues = $DB->get_records('bbbext_simple', ['bigbluebuttonbnid' => $instance->get_instance_id()]);
    if ($simplepluginvalues) {
        foreach ($simplepluginvalues as $pluginvalues) {
            foreach ($pluginvalues as $key => $value) {
                if ($key == 'meetingevents') {
                    echo "($name): {$key}: " . !empty($value) . "<br>";
                } else {
                    echo "($name): {$key}: {$value}<br>";
                }

            }
        }
    }
}
echo $OUTPUT->box_end();
$OUTPUT->footer();

