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

namespace mod_glossary\ai;

use aiplacement_modassist\action_process_response;
use context;
use core_ai\aiactions\base as action_base;

/**
 * Class mod_assist_info.
 *
 * @package    mod_glossary
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_assist_info extends \aiplacement_modassist\mod_assist_info {
    /**
     * Get the list of actions that are available for this placement.
     *
     * @return array
     */
    public function get_base_action_list(): array {
        return [
            \core_ai\aiactions\generate_text::class,
        ];
    }

    /**
     * Add the form definitions for the action.
     *
     * @param \MoodleQuickForm $mform
     * @param string $action
     * @return void
     * @throws \coding_exception
     */
    public function add_action_form_definitions(\MoodleQuickForm $mform, string $action): void {
        switch ($action) {
            case 'glossary-add-entry':
                $this->add_glossary_add_entry_form_definitions($mform);
                break;
        }
    }

    /**
     * Add the form definitions for the glossary add entry action.
     *
     * @param \MoodleQuickForm $mform
     * @return void
     * @throws \coding_exception
     */
    protected function add_glossary_add_entry_form_definitions(\MoodleQuickForm $mform): void {
        $mform->addElement('text',
            'topic',
            get_string('aiaction:addentries:topic', 'mod_glossary'), ['size' => 50]
        );
        $mform->setType('topic', PARAM_TEXT);
        $options = array_combine(range(1, 20), range(1, 20));
        $mform->addElement('select', 'itemcount', get_string('aiaction:addentries:itemcount', 'mod_glossary'), $options);
        $mform->setDefault('itemcount', 10);
        $mform->setType('itemscount', PARAM_INT);
    }

    /**
     * Get relevant AI action to retrieve content
     *
     * @param context $context
     * @param string $action
     * @param object $actiondata
     * @return action_base|null
     */
    public function get_ai_action(context $context, string $action, object $actiondata): ?action_base {
        global $DB;
        $cm = get_coursemodule_from_id('glossary', $context->instanceid);
        $entries = $DB->get_records_sql("SELECT e.*, u.firstname, u.lastname, u.email, u.picture
                                   FROM {glossary} g, {glossary_entries} e, {user} u
                             WHERE g.id = ?
                               AND e.glossaryid = g.id
                          ORDER BY e.timemodified ASC", [$cm->instance]);
        switch ($action) {
            case 'glossary-add-entry':
                $prompttext = $this->get_system_instructions($action) . "\n";
                $existingentries = join(",", array_column($entries, 'concept'));
                $prompttext .= "Existing entries: $existingentries\n";
                $prompttext .= "Number of entries to create: " . $actiondata->itemcount . "\n";
                $prompttext .= "Topic is:" . $actiondata->topic . "\n";
                $action = new \core_ai\aiactions\generate_text(
                    contextid: $context->id,
                    userid: $actiondata->userid,
                    prompttext: $prompttext,
                );
                return $action;
        }
        return null;
    }

    /**
     * Get system instructions for the action.
     *
     * @param string $action
     * @return string
     */
    protected function get_system_instructions(string $action): string {
        switch ($action) {
            case 'glossary-add-entry':
                return get_string('aiaction:addentries:instructions', 'mod_glossary');
        }
        return '';
    }

    /**
     * Process action response
     *
     * @param context $context
     * @param string $action
     * @param string $generatedcontent
     * @return action_process_response|null
     */
    public function process_response(context $context, string $action,
        string $generatedcontent): ?action_process_response {
        global $DB, $USER;
        $cm = get_coursemodule_from_id('glossary', $context->instanceid);
        switch ($action) {
            case 'glossary-add-entry':
                $newentries = explode("\n", $generatedcontent);
                // Parse as csv.
                foreach ($newentries as $entry) {
                    $entry = str_getcsv($entry, ";");
                    $entryobj = new \stdClass();
                    $entryobj->concept = trim($entry[0]);
                    $entryobj->definition = trim($entry[1]);
                    $entryobj->glossaryid = $cm->instance;
                    $entryobj->userid = $USER->id;
                    $entryobj->timecreated = time();
                    $entryobj->timemodified = time();
                    $DB->insert_record('glossary_entries', $entryobj);
                }
                return new action_process_response(
                    success:true,
                    actionname: $action,
                    successmessage: get_string('aiaction:addentries:success', 'mod_glossary', count($newentries))
                );
        }
        return null;
    }
}
