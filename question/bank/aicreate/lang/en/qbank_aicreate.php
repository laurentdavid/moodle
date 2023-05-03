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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Strings for component 'qbank_aicreate', language 'en'
 *
 * @package    qbank_aicreate
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Create questions with AI';
$string['privacy:metadata'] = 'The AI create question can create question automatically
via an AI API.';
$string['aicreate'] = 'Create with AI';
$string['createerror'] = 'Error while creating question.';
$string['aicreate_help'] = 'Write a prompt like "Create a question about capital cities in Europe."';
$string['aicreateheader'] = 'Create a question with AI';
$string['aiquestion'] = 'Question';

$string['prompt:format:aiken'] = 'Output should be in AIKEN Format and the questions should be multiple choices questions.

Examples of AIKEN Format.
"""
Question
A) Answer 1
B) Answer 2
C) Answer 3
D) Answer 4
ANSWER:  the right answer letter without parenthesis
"""';
