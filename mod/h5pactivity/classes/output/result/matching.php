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

/**
 * Contains class mod_h5pactivity\output\result\matching
 *
 * @package   mod_h5pactivity
 * @copyright 2020 Ferran Recio
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_h5pactivity\output\result;

defined('MOODLE_INTERNAL') || die();

use mod_h5pactivity\output\result;

/**
 * Class to display H5P matching result.
 *
 * @copyright 2020 Ferran Recio
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class matching extends result {

    /**
     * Return the options data structure.
     *
     * @return array of options
     */
    protected function export_options(): ?array {
        // Suppose H5P choices have only list of valid answers.
        $correctpattern = reset($this->correctpattern);

        $additionals = $this->additionals;

        // Get sources (options).
        if (isset($additionals->source)) {
            $draggables = $this->get_descriptions($additionals->source);
        } else {
            $draggables = [];
        }

        // Get dropzones.
        if (isset($additionals->target)) {
            $dropzones = $this->get_descriptions($additionals->target);
        } else {
            $dropzones = [];
        }
        $options = [];

        // Correct answers.
        foreach ($correctpattern as $pattern) {
            if (!is_array($pattern) || count($pattern) != 2) {
                continue;
            }
            // Modifications here we were switching dropzone and draggable depending on if
            // was defined or not and this lead to issue with reporting (MDL-71414)
            // We took reference here from :
            // https://github.com/h5p/h5p-php-report/blob/master/type-processors/matching-processor.class.php
            // i.e. draggable is index 1 and dropzone is index 0.
            if (isset($draggables[$pattern[1]]) && isset($dropzones[$pattern[0]])) {
                $currentdraggable = clone $draggables[$pattern[1]];
                $currentdropzone = $dropzones[$pattern[0]];
            } else {
                $currentdraggable = null;
            }
            if ($currentdraggable) {
                $currentdraggable->correctanswer = $this->get_answer(parent::TEXT, $currentdropzone->description);
                $currentdraggable->correctanswerid = $currentdropzone->id;
                $options[$currentdraggable->id . '/' . $currentdropzone->id] = $currentdraggable;
            }
        }

        // Sort options by keys.
        ksort($options);

        // User responses.
        foreach ($this->response as $response) {
            if (!is_array($response) || count($response) != 2) {
                continue;
            }
            if (isset($draggables[$response[1]]) && isset($dropzones[$response[0]])) {
                $currentdraggable = $draggables[$response[1]];
                $currentdropzone = $dropzones[$response[0]];
                $answer = $response[1];
            } else {
                $currentdraggable = null;
            }

            if ($currentdraggable) {
                $option = $options[$currentdraggable->id . '/' . $currentdropzone->id] ?? null;
                if (isset($option->correctanswerid) && $option->correctanswerid == $answer) {
                    $state = parent::CORRECT;
                } else {
                    $state = parent::INCORRECT;
                }
                if ($option) {
                    $option->useranswer = $this->get_answer($state, $currentdropzone->description);
                }

            }
        }
        return array_values($options);
    }

    /**
     * Return a label for result user options/choices
     *
     * Specific result types can override this method to customize
     * the result options table header.
     *
     * @return string to use in options table
     */
    protected function get_optionslabel(): string {
        return get_string('result_matching', 'mod_h5pactivity');
    }
}
