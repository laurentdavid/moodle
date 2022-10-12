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

namespace mod_data\external;

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use mod_data\local\importer\preset_existing_importer;
use mod_data\local\importer\preset_importer;
use mod_data\local\importer\preset_upload_importer;
use mod_data\manager;
use moodle_url;
use single_button;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * External service to check how we can apply preset to a given database
 *
 * Internal use mainly and for the apply preset/use preset dialog. It will compute
 * the fields to add, delete or keep compared to current database configuration.
 *
 * @package    mod_data
 * @copyright  2022 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class apply_presets_parameters extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
                'cmid' => new external_value(PARAM_INT, 'course module id', VALUE_REQUIRED),
                'presetpath' => new external_value(PARAM_RAW, 'path to the preset',
                        VALUE_DEFAULT, ''),
                'presetname' => new external_value(PARAM_RAW, 'preset name for existing presets',
                        VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Get preset informations
     *
     * @param int $cmid  the course module id
     * @param string|null $presetpath
     * @param string|null $presetname
     * @return array
     */
    public static function execute(
        int $cmid,
        ?string $presetpath = '',
        ?string $presetname = ''
    ): array {
        // Validate the cmid ID.
        [
            'cmid' => $cmid,
            'presetpath' => $presetpath,
            'presetname' => $presetname,
        ] = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'presetpath' => $presetpath,
            'presetname' => $presetname,
        ]);
        $cm = get_coursemodule_from_id('data', $cmid);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);

        $manager = manager::create_from_coursemodule($cm);
        if ($presetpath) {
            $importer = new preset_upload_importer($manager, $presetpath);
        } else {
            $importer = new preset_existing_importer($manager, $presetname);
        }
        $result = [
            'details' => json_encode(self::get_preset_parameters_from_importer($importer)),
        ];
        return $result;
    }

    /**
     * Describe the return structure of the external service.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
                'details' => new external_value(PARAM_RAW, 'Json encoded parameters for the popup dialog'),
        ]);
    }

    /**
     * Field separator string for preset list.
     */
    const FIELD_SEPARATOR = ', ';

    /**
     * Get preset parameters to display in apply preset dialog
     *
     * @param preset_importer $importer
     * @return array
     */
    public static function get_preset_parameters_from_importer(preset_importer $importer): array {
        global $PAGE;
        $formurl = '/mod/data/preset.php';
        $context = $importer->get_manager()->get_context();
        $PAGE->set_context($context);
        $renderer = $PAGE->get_renderer('mod_data');

        $manager = $importer->get_manager();
        $cm = $manager->get_coursemodule();
        $baseurl = new moodle_url($formurl, [
            'id' => $cm->id,
        ]);
        if (!$importer->needs_mapping()) {
            // No mapping needed, we can go ahead and import/use.
            $defaultmapping = $importer->get_default_mapping();
            $returnurl = new moodle_url($baseurl,
                    array_merge(
                            $importer->get_importer_mapping_parameters(),
                            ['overwritesettings' => true],
                            $defaultmapping)
            );
            $result = [
                'result' => true,
                'needsMapping' => false,
                'url' => $returnurl->out(false),
            ];
        } else {
            [
                preset_importer::FIELDS_TO_CREATE_KEY => $fieldstocreate,
                preset_importer::FIELDS_TO_DELETE_KEY => $fieldstodelete,
                preset_importer::FIELDS_TO_UPDATE_KEY => $fieldstoupdate,
            ] = $importer->get_import_action_from_settings();

            $mappingpagebutton = new single_button(
                new moodle_url($baseurl,
                    $importer->get_importer_mapping_parameters('import')),
                get_string('mapping:mapfields', 'mod_data'),
                'post'
            );
            $defaultmapping = $importer->get_default_mapping();
            $applypresetbutton = new single_button(
                new moodle_url($baseurl, array_merge(
                    $importer->get_importer_mapping_parameters(),
                    ['overwritesettings' => true],
                    $defaultmapping)),
                get_string('mapping:applypresets', 'mod_data'),
                'post',
                true,
            );
            $result = [
                'fieldsToCreate' => join(self::FIELD_SEPARATOR, array_keys($fieldstocreate)),
                'fieldsToUpdate' => join(self::FIELD_SEPARATOR, array_map(
                    function($field) {
                        return "$field->name [" . join(self::FIELD_SEPARATOR, array_keys($field->changedattributes)) . "]";
                    },
                    $fieldstoupdate
                )),
                'fieldsToDelete' => join(self::FIELD_SEPARATOR, array_keys($fieldstodelete)),
                'mappingPageParams' => $mappingpagebutton->export_for_template($renderer),
                'applyPresetParams' => $applypresetbutton->export_for_template($renderer),
                'needsMapping' => true,
            ];
        }
        return $result;
    }
}
