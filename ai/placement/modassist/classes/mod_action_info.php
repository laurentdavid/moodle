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

namespace aiplacement_modassist;

use core_ai\aiactions\base;

/**
 * Mod action Information.
 *
 * To be implemented by each module (can be several actions for the same module).
 *
 * @package    aiplacement_modassist
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_action_info {
    /**
     * Get the AI action class.
     *
     * @return base[] The AI action class.
     */
    public function get_ai_base_actions_classes(): array {
        return [];
    }
}
