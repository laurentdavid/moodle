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

namespace mod_bigbluebuttonbn\reportbuilder\datasource;

use core_reportbuilder\datasource;
use mod_bigbluebuttonbn\reportbuilder\local\entities\recording;

/**
 * Recordings datasource
 *
 * @copyright 2025 Laurent David <laurent.david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_bigbluebuttonbn
 */
class recordings extends datasource {

    /**
     * Return user-friendly name of the report source.
     */
    public static function get_name(): string {
        return get_string('view_section_title_recordings', 'mod_bigbluebuttonbn');
    }

    /**
     * Return the columns that will be added to the report upon creation.
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        $columns = [
            'playback',
            'name',
            'description',
            'preview',
            'date',
            'duration',
        ];
        return $columns;
    }

    /**
     * Return the filters that will be added to the report upon creation.
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return [
        ];
    }

    /**
     * Return the conditions that will be added to the report upon creation.
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [];
    }

    /**
     * Initialise report.
     */
    protected function initialise(): void {
        $recording = new recording();
        $recordingalias = $recording->get_table_alias('recording');
        $this->set_main_table('bigbluebuttonbn_recordings', $recordingalias);
        $this->add_entity($recording);
        $this->add_all_from_entities();
    }
}
