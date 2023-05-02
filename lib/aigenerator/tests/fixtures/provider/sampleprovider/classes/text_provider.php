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

namespace aigenerator_sampleprovider;

use core_aigenerator\ai_text_provider;

/**
 * Class ai_text_provider to generate text from a prompt
 *
 * @package    core_aigenerator
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_provider implements ai_text_provider {
    /**
     * Generate text completing the prompt
     *
     * @param string $prompt
     * @return string
     */
    public function generate_text(string $prompt): string {
        return "sample text";
    }
}
