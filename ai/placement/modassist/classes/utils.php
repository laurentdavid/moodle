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
use core_ai\manager;
use core_component;

/**
 * AI Placement course assist utils.
 *
 * @package    aiplacement_modassist
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {
    /**
     * Check if AI Placement course assist is available for the module.
     *
     * @param \context $context The context.
     * @return bool True if AI Placement course assist is available, false otherwise.
     */
    public static function is_mod_assist_available(\context $context): bool {
        [$plugintype, $pluginname] = explode('_', core_component::normalize_componentname('aiplacement_modassist'), 2);
        $manager = \core_plugin_manager::resolve_plugininfo_class($plugintype);
        if (!$manager::is_plugin_enabled($pluginname)) {
            return false;
        }
        if ($context->contextlevel !== CONTEXT_MODULE) {
            return false;
        }
        $modassistinfos = self::get_info_for_module($context);
        if (empty($modassistinfos)) {
            return false;
        }
        $providerbyactions = manager::get_providers_for_actions($modassistinfos->get_base_action_list(), true);
        foreach ($providerbyactions as $actionclass => $providers) {
            $capabilityname = basename(str_replace('\\', '/', $actionclass));
            if (!has_capability('aiplacement/modassist:' . $capabilityname, $context)
                || !manager::is_action_available($actionclass)
                || !manager::is_action_enabled('aiplacement_modassist', $actionclass)
                || empty($providers)
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get mod assist information for module.
     *
     * @param \context $context
     * @return mod_assist_info|null
     */
    public static function get_info_for_module(\context $context): ?mod_assist_info {
        $cm = self::get_course_module_from_context($context);
        if (empty($cm)) {
            return null;
        }
        $module = core_component::normalize_componentname($cm->modname);
        $modinfoclassname = '\\'. $module . '\\ai\\mod_assist_info';
        if (!class_exists($modinfoclassname)) {
            return null;
        }
        return new $modinfoclassname($context);
    }

    /**
     * Get course module for context
     *
     * @param context $context
     * @return \stdClass|null
     * @throws \coding_exception
     */
    public static function get_course_module_from_context(\context $context) {
        $cm = get_coursemodule_from_id('', $context->instanceid, 0, false, MUST_EXIST);
        if (empty($cm)) {
            return null;
        }
        return $cm;
    }

    /**
     * Get mod assist class for module
     *
     * @param \context $context
     * @param string $classname
     * @return string|null
     */
    public static function get_class_for_module(\context $context, string $classname): ?string {
        $cm = get_coursemodule_from_id('', $context->instanceid, 0, false, MUST_EXIST);
        if (empty($cm)) {
            return null;
        }
        $module = core_component::normalize_componentname($cm->modname);
        $classes = core_component::get_component_classes_in_namespace($module, 'ai\\output');
        foreach ($classes as $classfullname => $namespace) {
            if (strpos($classfullname, $classname) !== false) {
                return $classfullname;
            }
        }
        return null;
    }
}
