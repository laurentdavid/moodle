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

declare(strict_types=1);

namespace mod_bigbluebuttonbn\reportbuilder\local\systemreports;

use core\output\pix_icon;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\action;
use core_reportbuilder\local\report\column;
use core_reportbuilder\system_report;
use lang_string;
use mod_bigbluebuttonbn\output\recording_row_playback;
use mod_bigbluebuttonbn\output\recording_row_preview;
use mod_bigbluebuttonbn\reportbuilder\local\entities\recording;
use moodle_url;
use stdClass;

/**
 * Recordings entities system report
 *
 * @copyright 2025 Laurent David <laurent.david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_bigbluebuttonbn
 */
class recordings extends system_report {
    /**
     * Return the conditions that will be added to the report upon creation
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [];
    }

    /**
     * Initialise the entity
     *
     * @return base
     */
    public function initialise(): void {
        $recording = new recording();
        $recordingalias = $recording->get_table_alias('recording');
        $this->set_main_table('bigbluebuttonbn_recordings', $recordingalias);
        $this->add_entity($recording);
        $bigbluebuttonid = $this->get_parameter('bigbluebuttonid', 0, PARAM_INT);
        if (!empty($bigbluebuttonid)) {
            $this->add_base_condition_sql(
                "$recordingalias.bigbluebuttonbnid = :bigbluebuttonbnid",
                ['bigbluebuttonbnid' => $bigbluebuttonid],
            );
        }
        $this->add_columns();
        $this->add_filters();
        $this->add_actions();
        // Here we do this intentionally as any button inserted in the page results in a javascript error.
        // This is due to fact that if we insert it in an existing form this will nest the form and this is not allowed.
        $isdownloadable = $this->get_parameter('downloadable', true, PARAM_BOOL);
        $hasfilters = $this->get_parameter('hasfilters', false, PARAM_BOOL);
        $this->set_downloadable($isdownloadable);
        $this->set_filter_form_default($hasfilters);
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $recordingalias = $this->get_table_alias('recording');
        $columns = [];

        // Add the columns from the default entity.
        // I am not sure this is the best way to do it but it avoids reloading the full persistent entity (recordings)
        // at every column.
        $recordingfields = "$recordingalias.courseid,
            $recordingalias.bigbluebuttonbnid,
            $recordingalias.groupid,
            $recordingalias.recordingid,
            $recordingalias.headless,
            $recordingalias.imported,
            $recordingalias.status,
            $recordingalias.importeddata";
        $columns[] = (
        new column(
            'playback',
            new lang_string('view_recording_playback', 'mod_bigbluebuttonbn'),
            $this->get_entity_name(),
        )
        )->add_joins($this->get_joins())
            ->add_field("$recordingalias.id", 'id')
            ->add_fields($recordingfields)
            ->set_type(column::TYPE_INTEGER)
            ->set_callback(static function(int $recordingid, stdClass $row): string {
                global $PAGE;
                $recording = new \mod_bigbluebuttonbn\recording($recordingid, $row);
                $widget = new recording_row_playback($recording, null);
                return $PAGE->get_renderer('mod_bigbluebuttonbn')->render($widget);
            }
            );

        $columns[] = (
        new column(
            'name',
            new lang_string('view_recording_name', 'mod_bigbluebuttonbn'),
            $this->get_entity_name(),
        )
        )->add_joins($this->get_joins())
            ->add_field("$recordingalias.id", 'id')
            ->add_fields($recordingfields)
            ->set_type(column::TYPE_INTEGER)
            ->set_callback(static function(int $recordingid, stdClass $row): string {
                global $PAGE;
                $recording = new \mod_bigbluebuttonbn\recording($recordingid, $row);
                return $recording->get('name');
            }
            );
        $columns[] = (
        new column(
            'description',
            new lang_string('view_recording_description', 'mod_bigbluebuttonbn'),
            $this->get_entity_name(),
        )
        )->add_joins($this->get_joins())
            ->add_field("$recordingalias.id", 'id')
            ->add_fields($recordingfields)
            ->set_type(column::TYPE_INTEGER)
            ->set_callback(static function(int $recordingid, stdClass $row): string {
                global $PAGE;
                $recording = new \mod_bigbluebuttonbn\recording($recordingid, $row);
                return $recording->get('description');
            }
            );
        $columns[] = (
        new column(
            'preview',
            new lang_string('view_recording_preview', 'mod_bigbluebuttonbn'),
            $this->get_entity_name(),
        )
        )->add_joins($this->get_joins())
            ->add_field("$recordingalias.id", 'id')
            ->add_fields($recordingfields)
            ->set_type(column::TYPE_INTEGER)
            ->set_callback(static function(int $recordingid, stdClass $row): string {
                global $PAGE;
                $recording = new \mod_bigbluebuttonbn\recording($recordingid, $row);
                $widget = new recording_row_preview($recording);
                return $PAGE->get_renderer('mod_bigbluebuttonbn')->render($widget);
            }
            );

        $columns[] = (
        new column(
            'date',
            new lang_string('view_recording_date', 'mod_bigbluebuttonbn'),
            $this->get_entity_name(),
        )
        )->add_joins($this->get_joins())
            ->add_field("$recordingalias.id", 'id')
            ->add_fields($recordingfields)
            ->set_type(column::TYPE_INTEGER)
            ->set_callback(static function(int $recordingid, stdClass $row): string {
                $recording = new \mod_bigbluebuttonbn\recording($recordingid, $row);
                return $recording->get('starttime') ?? '0';
            }
            );

        $columns[] = (
        new column(
            'duration',
            new lang_string('view_recording_duration', 'mod_bigbluebuttonbn'),
            $this->get_entity_name(),
        )
        )->add_field("$recordingalias.id", 'id')
            ->add_field("$recordingalias.id", 'id')
            ->add_fields($recordingfields)
            ->set_type(column::TYPE_INTEGER)
            ->set_callback(static function(int $recordingid, stdClass $row): string {
                $recording = new \mod_bigbluebuttonbn\recording($recordingid, $row);
                $playbacks = $recording->get('playbacks');
                if (empty($playbacks)) {
                    return '0';
                }
                foreach ($playbacks as $playback) {
                    // Ignore restricted playbacks.
                    if (array_key_exists('restricted', $playback) && strtolower($playback['restricted']) == 'true') {
                        continue;
                    }

                    // Take the length form the fist playback with an actual value.
                    if (!empty($playback['length'])) {
                        return $playback['length'];
                    }
                }
                return '0';

            }
            );
        return $columns;
    }

    /**
     * Adds the columns we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    protected function add_columns(): void {
        $columns = [
            'recording:playback',
            'recording:name',
            'recording:description',
            'recording:preview',
            'recording:date',
            'recording:duration',
        ];
        $this->add_columns_from_entities($columns);

        // Default sorting.
        $this->set_initial_sort_column('recording:name', SORT_ASC);
    }

    /**
     * Adds the filters we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    protected function add_filters(): void {
        $filters = [];
        $this->add_filters_from_entities($filters);
    }

    protected function can_view(): bool {
        return true; // TODO: Check if the user can view this report.
    }

    /**
     * Add actions
     *
     * @return void
     * @throws \core\exception\coding_exception
     */
    protected function add_actions(): void {
        global $USER;
        $alias = $this->get_main_table_alias();
        foreach ($this::TOOL_ACTION_DEFINITIONS as $actionname => $actiondefinition) {
            $class= 'iconsmall action-icon';
            if (isset($actiondefinition['disabled'])) {
                $class .= ' fa-' . $actiondefinition['disabled'] .' disabled';
            }
            $action = new action(
                new moodle_url('#'),
                new pix_icon('t/' . $actiondefinition['icon'], ''),
                [
                    'data-action' => $actiondefinition['action'],
                    'id' => ':id',
                    'data-require-confirmation' => !empty($actiondefinition['requireconfirmation']),
                    'data-bigbluebuttonbn-id' => ':bigbluebuttonbnid',
                    'class' => $class,
                ],
                false,
                new lang_string('view_recording_list_actionbar_' . $actionname, 'mod_bigbluebuttonbn')
            );
            $this->add_action($action);
        }
    }


    /**
     * @var array TOOLS_DEFINITION a list of definition for the the specific tools
     */
    const TOOL_ACTION_DEFINITIONS = [
        'protect' => [
            'action' => 'unprotect',
            'icon' => 'lock',
            'hidewhen' => '!protected',
            'requireconfirmation' => true,
            'disablewhen' => 'imported'
        ],
        'unprotect' => [
            'action' => 'protect',
            'icon' => 'unlock',
            'hidewhen' => 'protected',
            'requireconfirmation' => true,
            'disablewhen' => 'imported'
        ],
        'publish' => [
            'action' => 'publish',
            'icon' => 'show',
            'hidewhen' => 'published',
            'requireconfirmation' => true,
            'disablewhen' => 'imported'
        ],
        'unpublish' => [
            'action' => 'unpublish',
            'icon' => 'hide',
            'hidewhen' => '!published',
            'requireconfirmation' => true,
            'disablewhen' => 'imported'
        ],
        'delete' => [
            'action' => 'delete',
            'icon' => 'trash',
            'requireconfirmation' => true
        ],
        'import' => [
            'action' => 'import',
            'icon' => 'import',
        ]
    ];
}
