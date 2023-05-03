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
namespace qbank_aicreate\form;

defined('MOODLE_INTERNAL') || die();

use moodle_exception;
use moodleform;
use stdClass;

require_once($CFG->libdir . '/formslib.php');

/**
 * Form to create questions into the question bank from prompt.
 *
 * @package    qbank_aicreate
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_create_form extends moodleform {
    /**
     * Build the form definition.
     *
     * This adds all the form fields that the manage categories feature needs.
     *
     * @throws \coding_exception
     */
    protected function definition() {
        global $OUTPUT;

        $mform = $this->_form;

        $defaultcategory = $this->_customdata['defaultcategory'];
        $contexts = $this->_customdata['contexts'];
        // Import options.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('questioncategory', 'category', get_string('importcategory', 'question'), compact('contexts'));
        $mform->setDefault('category', $defaultcategory);
        $mform->addHelpButton('category', 'importcategory', 'question');

        $matchgrades = [];
        $matchgrades['error'] = get_string('matchgradeserror', 'question');
        $matchgrades['nearest'] = get_string('matchgradesnearest', 'question');
        $mform->addElement('select', 'matchgrades', get_string('matchgrades', 'question'), $matchgrades);
        $mform->addHelpButton('matchgrades', 'matchgrades', 'question');
        $mform->setDefault('matchgrades', 'error');

        $mform->addElement('selectyesno', 'stoponerror', get_string('stoponerror', 'question'));
        $mform->setDefault('stoponerror', 1);
        $mform->addHelpButton('stoponerror', 'stoponerror', 'question');

        // The file to import.
        $mform->addElement('header', 'aicreateheader', get_string('aicreateheader', 'qbank_aicreate'));

        $mform->addElement('editor', 'questionprompt', get_string('aiquestion', 'qbank_aicreate'));
        $mform->setType('questionprompt', PARAM_TEXT);
        $mform->addRule('questionprompt', null, 'required', null, 'client');

        $mform->addElement('hidden', 'promptresultfileid');
        $mform->setType('promptresultfileid', PARAM_INT);

        $mform->createElement('hidden', 'format');
        $mform->setType('format', PARAM_TEXT);
        // Submit button.
        $mform->addElement('submit', 'submitbutton', get_string('aicreate', 'qbank_aicreate'));
    }

    /**
     * Send info to the AI
     *
     * @return void
     */
    public function definition_after_data() {
        global $USER;
        $mform = $this->_form;
        $prompt = $mform->getElementValue('questionprompt');
        $aiapi = \core_aigenerator\api::load();
        $prompt = html_to_text(format_text($prompt['text'], $prompt['format']));
        if (!empty(trim($prompt))) {
            $prompt .= "\n" . get_string('prompt:format:aiken', 'qbank_aicreate');
            $result = $aiapi->get_text_provider()->generate_text($prompt);
            $fs = get_file_storage();
            // Create draft file to import.
            $draftid = file_get_unused_draft_itemid();
            $filerecord = [
                'component' => 'user',
                'filearea' => 'draft',
                'contextid' => \context_user::instance($USER->id)->id, 'itemid' => $draftid,
                'filename' => 'question.txt', 'filepath' => '/'
            ];
            $file = $fs->create_file_from_string($filerecord, $result);
            $mform->setConstants(['promptresultfileid' => $file->get_id(), 'format' => 'aiken']);
        }
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     * @throws \dml_exception|\coding_exception|moodle_exception
     */
    public function validation($data, $files) {
        global $CFG;
        $errors = parent::validation($data, $files);
        if (empty($data['questionprompt'])) {
            $errors['questionprompt'] = get_string('required');
            return $errors;
        }

        $formatfile = $CFG->dirroot . '/question/format/' . $data['format'] . '/format.php';
        if (!is_readable($formatfile)) {
            throw new moodle_exception('formatnotfound', 'question', '', $data['format']);
        }

        require_once($formatfile);

        $classname = 'qformat_' . $data['format'];
        $qformat = new $classname();
        $fs = get_file_storage();
        $file = $fs->get_file_by_id($data['promptresultfileid']);
        if (!$qformat->can_import_file($file)) {
            $a = new stdClass();
            $a->actualtype = $file->get_mimetype();
            $a->expectedtype = $qformat->mime_type();
            $errors['newfile'] = get_string('importwrongfiletype', 'question', $a);
            return $errors;
        }

        $fileerrors = $qformat->validate_file($file);
        if ($fileerrors) {
            $errors['questionprompt'] = $fileerrors;
        }
        return $errors;
    }
}
