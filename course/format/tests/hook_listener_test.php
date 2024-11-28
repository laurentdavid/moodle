<?php
// This file is part of Moodle - https://moodle.org/
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

namespace core_courseformat;

/**
 * Test hook for external routing
 *
 * @coversDefaultClass \core_courseformat\hook_listener
 *
 * @package   core_courseformat
 * @copyright 2024 Laurent David (laurent.david@moodle.org)
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class hook_listener_test extends \advanced_testcase {

    /**
     * Test hook dispatch and propagation.
     * @covers \core_courseformat\hook_listener::before_course_viewed
     *
     */
    public function test_basic_hook_deprecation(): void {
        $course = new \stdClass();
        $course->id = 1;
        $course->fullname = 'Test Course';
        $hook = new \core_course\hook\before_course_viewed($course);
        \core\hook\manager::get_instance()->dispatch($hook);

        $this->assertDebuggingCalled('Deprecation: core_courseformat\hook_listener::before_course_viewed has been' .
            ' deprecated since 5.0. Use core_courseformat\hook\hook_listener::before_course_viewed instead. See MDL-83764 for' .
            ' more information.');
    }
}
