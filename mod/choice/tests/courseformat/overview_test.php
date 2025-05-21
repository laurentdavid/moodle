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

namespace mod_choice\courseformat;

use core\exception\coding_exception;
use core_courseformat\local\overview\overviewfactory;

/**
 * Tests for Choice integration.
 *
 * @covers \mod_choice\courseformat\overview
 * @package    mod_choice
 * @category   test
 * @copyright  2025 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class overview_test extends \advanced_testcase {
    /**
     * Test get_extra_status_for_user method.
     *
     * @covers ::get_extra_status_for_user
     * @dataProvider data_provider_get_extra_status_for_user
     * @param string $user
     * @param bool $answered
     * @param string|null $status
     */
    public function test_get_extra_status_for_user(string $user, bool $answered, ?string $status = null): void {
        global $DB, $OUTPUT;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $activity = $this->getDataGenerator()->create_module(
            'choice',
            ['course' => $course->id, 'allowmultiple' => 1, 'option' => ['A', 'B', 'C']],
        );
        $cm = get_fast_modinfo($course)->get_cm($activity->cmid);

        $currentuser = ($user == 'teacher') ? $teacher : $student;
        $this->setUser($currentuser);

        if ($answered) {
            $choices = $DB->get_fieldset('choice_options', 'id', ['choiceid' => $activity->id]);
            array_shift($choices); // Remove the first choice.
            $this->getDataGenerator()->get_plugin_generator('choice')->create_response([
                    'choiceid' => $activity->id,
                    'responses' => $choices,
                    'userid' => $currentuser->id,
                ]
            );
        }
        $overview = overviewfactory::create($cm);
        $reflection = new \ReflectionClass($overview);
        $method = $reflection->getMethod('get_extra_status_for_user');
        $method->setAccessible(true);
        $item = $method->invoke($overview);
        if (is_null($status)) {
            $this->assertNull($item);
            return;
        }
        $this->assertEquals($answered, $item->get_value());
        $content = $item->get_content();
        if (!is_string($content)) {
            $content = $OUTPUT->render($content);
        }
        $this->assertStringContainsString($status, html_to_text($content));
    }

    /**
     * Data provider for test_get_extra_status_for_user.
     *
     * @return array
     */
    public static function data_provider_get_extra_status_for_user(): array {
        return [
            'teacher view answered' => [
                'user' => 'teacher',
                'answered' => true,
                'status' => null,
            ],
            'teacher view not answered' => [
                'user' => 'teacher',
                'answered' => false,
                'status' => null,
            ],
            'student view answered' => [
                'user' => 'student',
                'answered' => true,
                'status' => 'Answered',
            ],
            'student view not answered' => [
                'user' => 'student',
                'answered' => false,
                'status' => '-',
            ],
        ];
    }

    /**
     * Test get_extra_status_for_user method.
     *
     * @param \DateTime|null $date
     * @param string $expectedcontent
     *
     * @covers ::get_due_date_overview
     * @dataProvider data_provider_get_due_date_overview
     */
    public function test_get_due_date_overview(\DateTime|null $date, string $expectedcontent): void {
        global $OUTPUT;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $choicetemplate = ['course' => $course->id, 'allowmultiple' => 1, 'option' => ['A', 'B', 'C']];
        if ($date) {
            $choicetemplate['timeclose'] = $date->getTimestamp();
        }
        $activity = $this->getDataGenerator()->create_module(
            'choice',
            $choicetemplate,
        );
        $cm = get_fast_modinfo($course)->get_cm($activity->cmid);
        $this->setUser($student);
        $overview = overviewfactory::create($cm);
        $duedate = $overview->get_due_date_overview()->get_content();
        if (!is_string($duedate)) {
            $duedate = $OUTPUT->render($duedate);
        }
        $this->assertStringContainsString($expectedcontent, $duedate);
    }


    /**
     * Data provider for test_get_due_date_overview.
     *
     * @return array
     */
    public static function data_provider_get_due_date_overview(): array {
        return [
            'tomorrow' => [
                'date' => new \DateTime('tomorrow'),
                'expectedcontent' => 'Tomorrow',
            ],
            'yesterday' => [
                'date' => new \DateTime('yesterday'),
                'expectedcontent' => 'Yesterday',
            ],
            'today' => [
                'date' => new \DateTime('today'),
                'expectedcontent' => 'Today',
            ],
            'No date' => [
                'date' => null,
                'expectedcontent' => '-',
            ],
        ];
    }

}
