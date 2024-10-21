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

use context_module;
use core_ai\aiactions\responses\response_base;

/**
 * Class mod_assist_info.
 *
 * @package    aiplacement_modassist
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
        $mform->addElement('text', 'addentryquery', get_string('ai:addentryquery', 'mod_glossary'), array('size' => 50));
        $mform->setType('addentryquery', PARAM_TEXT);
        $mform->addElement('text', 'itemcount', get_string('ai:itemcount', 'mod_glossary'));
        $mform->setType('itemscount', PARAM_INT);
    }

    /**
     * Process action
     *
     * @param context_module $context
     * @param string $action
     * @param object $actiondata
     * @return response_base|null response from the action
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_action(context_module $context, string $action, object $actiondata): ?response_base {
        global $DB;
        $cm = get_coursemodule_from_id('glossary', $context->instanceid);
        $entries = $DB->get_records_sql("SELECT e.*, u.firstname, u.lastname, u.email, u.picture
                                   FROM {glossary} g, {glossary_entries} e, {user} u
                             WHERE g.id = ?
                               AND e.glossaryid = g.id
                          ORDER BY e.timemodified ASC", [$cm->instance]);
        switch ($action) {
            case 'glossary-add-entry':
                // Prepare the action.
                $prompttext = $this->get_system_instructions($action) . "\n";
                $existingentries = join(",", array_column($entries, 'concept'));
                $prompttext .= "Existing entries: $existingentries\n";
                $prompttext .= "Number of entries to create: " . $actiondata->itemcount . "\n";
                $prompttext .= "Topic is:" . $actiondata->addentryquery . "\n";
                $action = new \core_ai\aiactions\generate_text(
                    contextid: $context->id,
                    userid: $actiondata->userid,
                    prompttext: $prompttext,
                );

                // Send the action to the AI manager.
                $manager = new \core_ai\manager();
                $response =  $manager->process_action($action);
                if ($response->get_success()) {
                    $responsetext = $response->get_response_data()['generatedcontent'] ?? '';
                    $newentries = explode("\n", $responsetext);
                    foreach ($newentries as $entry) {
                        $entry = explode(";", $entry);
                        $entryobj = new \stdClass();
                        $entryobj->concept = trim(trim($entry[0], '"'));
                        $entryobj->definition = trim(trim($entry[1], '"'));
                        $entryobj->glossaryid = $cm->instance;
                        $entryobj->userid = $actiondata->userid;
                        $entryobj->timecreated = time();
                        $entryobj->timemodified = time();
                        $DB->insert_record('glossary_entries', $entryobj);
                    }
                }
                return $response;
        }
        return null;
    }

    protected function get_system_instructions(string $action): string {
        switch ($action) {
            case 'glossary-add-entry':
                return "You should provide a set of new entries to the glossary on the specified topic. The format of the
                entries should be as follows (csv): \"term\";\"definition\". Each entry should be separated by a new line.
                You should ignore existing entries.";
        }
        return '';
    }
}