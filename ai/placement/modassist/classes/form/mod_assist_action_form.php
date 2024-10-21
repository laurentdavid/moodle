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

namespace aiplacement_modassist\form;

use aiplacement_modassist\utils;
use context;
use core_component;
use core_form\dynamic_form;
use moodle_exception;
use moodle_url;

/**
 * Mod assist form.
 *
 * @package    aiplacement_modassist
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_assist_action_form extends dynamic_form {

    /**
     * Process the form submission
     *
     * @return array
     * @throws moodle_exception
     */
    public function process_dynamic_submission(): array {
        $this->check_access_for_dynamic_submission();
        $modinfo = utils::get_info_for_module($this->get_context_for_dynamic_submission());
        if (empty($modinfo)) {
            return [
                'result' => false,
            ];
        }
        return [
            'result' => true,
            'actiondata' => $this->get_data(),
        ];
    }

    /**
     * Get context
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        $cmid = $this->optional_param('cmid', null, PARAM_INT);
        $module = $this->optional_param('component', null, PARAM_COMPONENT);
        $cm = get_coursemodule_from_id($module, $cmid);
        $context = \context_module::instance($cm->id);
        return $context;
    }

    /**
     * Set data
     *
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {
        $data = (object) [
            'cmid' => $this->optional_param('cmid', 0, PARAM_INT),
            'userid' => $this->optional_param('userid', 0, PARAM_INT),
            'action' => $this->optional_param('action', '', PARAM_ALPHANUMEXT),
            'component' => $this->optional_param('component', '', PARAM_COMPONENT),
        ];
        $this->set_data($data);
    }

    /**
     * Has access ?
     *
     * @return void
     * @throws moodle_exception
     */
    protected function check_access_for_dynamic_submission(): void {
        if (!has_capability('aiplacement/modassist:generate_text', $this->get_context_for_dynamic_submission())) {
            throw new moodle_exception(get_string('cannotgenerate', 'aiplacement_modassist'), '');
        }
    }

    /**
     * Get page URL
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $cmid = $this->optional_param('cmid', null, PARAM_INT);
        $component = $this->optional_param('component', null, PARAM_COMPONENT);
        $component = core_component::normalize_componentname($component);
        [$plugintype, $pluginname] = explode('_', $component, 2);
        if ($plugintype !== 'mod') {
            throw new moodle_exception('invalidcomponent', 'aiplacement_modassist');
        }
        return new moodle_url('/mod/view.php', ['id' => $cmid]);
    }

    /**
     * Form definition
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'component');
        $mform->setType('component', PARAM_COMPONENT);
        $modinfo = utils::get_info_for_module($this->get_context_for_dynamic_submission());
        if (empty($modinfo)) {
            return;
        }
        $modinfo->add_action_form_definitions($mform, $this->get_action());
    }

    /**
     * Get action
     *
     * @return string
     */
    private function get_action(): string {
        return $this->optional_param('action', '', PARAM_ALPHANUMEXT);
    }
}
