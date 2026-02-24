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

namespace core_course\route\controller;

use core\router\route_loader_interface;
use core\tests\router\route_testcase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Restricted module controller tests.
 *
 * @package     core_course
 * @copyright   2026 Laurent David <laurent.david@moodle.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
#[CoversClass(\core_course\route\controller\restricted_module::class)]
final class restricted_module_test extends route_testcase {
    /**
     * Test that users not enrolled in the module course are redirected by require_login.
     */
    public function test_restricted_module_requires_enrolment_in_cm_course(): void {
        global $PAGE;
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        set_config('enableavailability', 1);
        $course = $generator->create_course();
        $PAGE->reset_theme_and_output(); // Reset the $PAGE theme because create_course will set it and on the next call
        // to require_login, we will try to set the course and ensure_theme_not_set will just throw an exception.

        $tomorrow = strtotime('tomorrow');
        $availability = json_encode([
            'op' => '&',
            'c' => [
                [
                    'type' => 'date',
                    'd' => '>=',
                    't' => $tomorrow,
                ],
            ],
            'showc' => [true],
        ]);
        $module = $generator->create_module('page', [
            'course' => $course->id,
            'availability' => $availability,
        ]);

        $unenrolleduser = $generator->create_user();
        $this->setUser($unenrolleduser);
        $response = $this->process_request(
            'GET',
            "course/cms/{$module->cmid}/restricted",
            route_loader_interface::ROUTE_GROUP_PAGE
        );
        // This should be a redirection but as we are in console, we will get a 500 error because of the redirection exception.
        $this->assertEquals(500, $response->getStatusCode());
    }
}
