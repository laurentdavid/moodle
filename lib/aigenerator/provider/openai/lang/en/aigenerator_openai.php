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
 * Language File.
 *
 * @package   aigenerator_openai
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['apikey'] = 'API Key';
$string['apikey_desc'] = 'API Key';
$string['imagesizeerrorwidthheight'] = "Specified width and height should be equal for Image generation";
$string['invalidsize'] = "Specified width and height should be 256, 512 or 1024.";
$string['orgid'] = 'Organisation ID';
$string['orgid_desc'] = 'Organisation ID (optional)';
$string['openaierror'] = 'Error when calling OpenAI API: (Error {$a->errorcode}) - {$a->error}.';
$string['pluginname'] = 'Open AI Generator';
$string['textmaxtoken'] = 'Max token for text queries';
$string['textmaxtoken_desc'] = 'Max token for text queries';
$string['textmodel'] = 'Text model';
$string['textmodel_desc'] = 'Text model (Default: text-davinci-003)';
$string['texttemperature'] = 'Text temperature';
$string['texttemperature_desc'] = 'Text original temperature';
