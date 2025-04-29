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

namespace mod_bigbluebuttonbn\reportbuilder\local\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\column;
use lang_string;
use mod_bigbluebuttonbn\output\recording_row_playback;
use mod_bigbluebuttonbn\output\recording_row_preview;
use stdClass;

/**
 * Recordings entity
 *
 * @copyright 2025 Laurent David <laurent.david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_bigbluebuttonbn
 */
class recording extends base {

    /**
     * Initialise the entity
     *
     * @return base
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        // All the filters defined by the entity can also be used as conditions.
        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }
        return $this;
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

    private function get_all_filters() {
        return [];
    }

    /**
     * Initialise the entity
     *
     * @return base
     */
    protected function get_default_tables(): array {
        return [
            'bigbluebuttonbn_recordings' => 'recording',
            'bigbluebuttonbn' => 'bigbluebuttonbn',
        ];
    }

    /**
     * Returns the name of the entity
     *
     * @return string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('view_recording', 'mod_bigbluebuttonbn');
    }
}
