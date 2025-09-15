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

namespace mod_forum\external;

use mod_forum\output\courseformat\toggle;

/**
 * Tests for activityname_exporter.
 *
 * @package    mod_forum
 * @category   test
 * @copyright  2025 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \mod_forum\external\toggle_exporter
 */
final class toggle_exporter_test extends \advanced_testcase {
    /**
     * Test export method.
     */
    public function test_export(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'editingteacher');

        $mod = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);

        $this->setUser($user);
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($mod->cmid);

        $format = course_get_format($course);
        $renderer = \core\di::get(\core\output\renderer_helper::class)->get_core_renderer();

        $source = new toggle($cm, label: 'Label', checked: true, disabled: false);

        $exporter = new toggle_exporter($source, ['context' => \context_system::instance()]);
        $data = $exporter->export($renderer);

        $this->assertObjectHasProperty('type', $data);
        $this->assertObjectHasProperty('checked', $data);
        $this->assertObjectHasProperty('disabled', $data);
        $this->assertObjectHasProperty('label', $data);
        $this->assertCount(4, get_object_vars($data));

        $expected = [
            'type' => 'forum-track-toggle',
            'checked' => true,
            'disabled' => false,
            'label' => 'Label',
        ];

        foreach ($expected as $property => $value) {
            $this->assertEquals($value, $data->$property, "Property '$property' does not match expected value.");
        }
    }
}
