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
 * Open AI Generator settings
 *
 * @package   aigenerator_openai
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    // API Key.
    $name = new lang_string('apikey', 'aigenerator_openai');
    $desc = new lang_string('apikey_desc', 'aigenerator_openai');
    $settings->add(new admin_setting_configpasswordunmask('aigenerator_openai/apikey', $name, $desc, ''));

    // Org ID (optional).
    $name = new lang_string('orgid', 'aigenerator_openai');
    $desc = new lang_string('orgid_desc', 'aigenerator_openai');
    $settings->add(new admin_setting_configtext('aigenerator_openai/orgid', $name, $desc, '', PARAM_TEXT));

    // Max tokens.
    $name = new lang_string('textmaxtoken', 'aigenerator_openai');
    $desc = new lang_string('textmaxtoken_desc', 'aigenerator_openai');
    $settings->add(new admin_setting_configtext('aigenerator_openai/textmaxtoken', $name, $desc,
        \aigenerator_openai\text_provider::DEFAULT_MAX_TOKEN, PARAM_INT));
    // Model.
    $name = new lang_string('textmodel', 'aigenerator_openai');
    $desc = new lang_string('textmodel_desc', 'aigenerator_openai');
    $settings->add(new admin_setting_configtext('aigenerator_openai/textmaxtoken', $name, $desc,
        \aigenerator_openai\text_provider::DEFAULT_MODEL, PARAM_TEXT));

    $name = new lang_string('texttemperature', 'aigenerator_openai');
    $desc = new lang_string('texttemperature_desc', 'aigenerator_openai');
    $settings->add(new admin_setting_configtext('aigenerator_openai/texttemperature', $name, $desc,
        \aigenerator_openai\text_provider::DEFAULT_TEMPERATURE, PARAM_FLOAT));
}
