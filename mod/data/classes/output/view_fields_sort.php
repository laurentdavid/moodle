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

namespace mod_data\output;

use mod_data\manager;
use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * Field sort form output class
 *
 * @package    mod_data
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_fields_sort implements templatable, renderable {
    /**
     * @var \moodle_url $currenturl
     */
    private $currenturl;
    /**
     * Current manager
     *
     * @var manager $manager
     */
    private $manager;

    /**
     * Setup the field sort
     *
     * @param manager $manager
     */
    public function __construct(manager $manager, ?moodle_url $currenturl = null) {
        $this->manager = $manager;
        $this->currenturl = $currenturl ?? new moodle_url('/mod/data/view.php', ['id' => $manager->get_coursemodule()->id]);
    }

    /**
     * Export data for template
     *
     * @param renderer_base $output
     * @return array|\stdClass|void
     */
    public function export_for_template(renderer_base $output) {
        global $DB;
        $instance = $this->manager->get_instance();
        $defaultsortid = $instance->defaultsort;
        $defaultsortdirid = $instance->defaultsortdir;
        $currentparams = [];
        foreach ($this->currenturl->params() as $key => $value) {
            if (in_array($key, ['defaultsort', 'defaultsortdir'])) {
                continue; // Do not add the actual form fields.
            }
            $currentparams[] = ['key' => $key, 'value' => $value];
        }
        $data = [
            'formurl' => $this->currenturl->out_omit_querystring(),
            'cmid' => $this->manager->get_coursemodule()->id,
            'currentparams' => $currentparams,
            'sesskey' => sesskey(),
        ];
        $fields = $DB->get_records('data_fields', ['dataid' => $this->manager->get_instance()->id]);
        $data['sorts'] = [];

        if ($fields) {
            $options = [];
            foreach ($fields as $field) {
                $fielddata = ['id' => $field->id, 'label' => $field->name];
                if ($defaultsortid == $field->id) {
                    $fielddata['selected'] = true;
                }
                $options[] = $fielddata;
            }
            $data['sorts'][] = [
                'groupname' => get_string('fields', 'mod_data'),
                'options' => $options
            ];
        }
        $timeadded = [
            'id' => DATA_TIMEADDED,
            'label' => get_string('timeadded', 'data')
        ];
        if ($defaultsortid == DATA_TIMEADDED) {
            $timeadded['selected'] = true;
        }
        $data['sorts'][] = [
            'groupname' => get_string('other', 'mod_data'),
            'options' => [
                $timeadded
            ]
        ];
        $data['sortdir'] = [
            [
                'id' => 0,
                'label' => get_string('ascending', 'mod_data')
            ],
            [
                'id' => 1,
                'label' => get_string('descending', 'mod_data')
            ]
        ];
        $data['sortdir'][$defaultsortdirid]['selected'] = true;
        return $data;
    }
}
