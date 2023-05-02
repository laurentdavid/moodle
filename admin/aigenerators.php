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
 * A page to manage aigenerator plugins.
 *
 * @package   core_admin
 * @copyright 2023 Laurent David <laurent.david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
global $CFG, $PAGE;
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

$action = required_param('action', PARAM_ALPHANUMEXT);
$plugin = required_param('plugin', PARAM_PLUGIN);

$PAGE->set_url('/admin/aigenerators.php', ['action' => $action, 'aigenerator' => $plugin]);
$PAGE->set_context(context_system::instance());

require_admin();
require_sesskey();

$returnurl = new moodle_url('/admin/settings.php', ['section' => 'manageaigenerators']);

// Get currently installed and enabled auth plugins.
$availableaigenerators = core_plugin_manager::instance()->get_plugins_of_type('aigenerator');
if (!empty($plugin) && empty($availableaigenerators[$plugin])) {
    throw new moodle_exception('aigeneratorprovidernotfound', 'core_aigenerator', $returnurl, $plugin);
}

$activeaigenerators = explode(',', $CFG->aigenerators);
foreach ($activeaigenerators as $key => $active) {
    if (empty($availableaigenerators[$active])) {
        unset($activeaigenerators[$key]);
    }
}

switch ($action) {
    case 'disable':
        // Remove from enabled list.
        $class = \core_plugin_manager::resolve_plugininfo_class('aigenerator');
        $class::enable_plugin($plugin, false);
        break;

    case 'enable':
        // Add to enabled list.
        if (!in_array($plugin, $activeaigenerators)) {
            $class = \core_plugin_manager::resolve_plugininfo_class('aigenerator');
            $class::enable_plugin($plugin, true);
        }
        break;

    case 'down':
        $key = array_search($plugin, $activeaigenerators);
        if ($key !== false) {
            // Move down the list.
            if ($key < (count($activeaigenerators) - 1)) {
                $fsave = $activeaigenerators[$key];
                $activeaigenerators[$key] = $activeaigenerators[$key + 1];
                $activeaigenerators[$key + 1] = $fsave;
                add_to_config_log('aigenerator_position', $key, $key + 1, $plugin);
                set_config('aigenerators', implode(',', $activeaigenerators));
                core_plugin_manager::reset_caches();
            }
        }
        break;

    case 'up':
        $key = array_search($plugin, $activeaigenerators);
        if ($key !== false) {
            // Move up the list.
            if ($key >= 1) {
                $fsave = $activeaigenerators[$key];
                $activeaigenerators[$key] = $activeaigenerators[$key - 1];
                $activeaigenerators[$key - 1] = $fsave;
                add_to_config_log('aigenerator_position', $key, $key - 1, $plugin);
                set_config('aigenerators', implode(',', $activeaigenerators));
                core_plugin_manager::reset_caches();
            }
        }
        break;

    default:
        break;
}

redirect($returnurl);
