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

namespace mod_choice;

/**
 * Generator tests class.
 *
 * @package    mod_choice
 * @copyright  2025 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \mod_choice\manager
 */
final class manager_test extends \advanced_testcase {
    /**
     * @var \stdClass course record.
     */
    protected \stdClass $course;
    /**
     * @var \stdClass course module record.
     */
    protected \stdClass $instance;

    /**
     * Set up the test environment.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->course = $this->getDataGenerator()->create_course();
        $this->instance = $this->getDataGenerator()->create_module('choice', [
            'course' => $this->course,
            'option' => ['A', 'B', 'C'],
        ]);
    }

    /**
     * Test creating a manager instance from an instance record.
     *
     * @covers \mod_choice\manager::create_from_instance
     */
    public function test_create_manager_instance_from_instance_record(): void {
        $manager = \mod_choice\manager::create_from_instance($this->instance);
        $this->assertNotNull($manager);
    }

    /**
     * Test creating a manager instance from a course module.
     *
     * @covers \mod_choice\manager::create_from_coursemodule
     */
    public function test_create_manager_instance_from_coursemodule(): void {
        $cm = get_fast_modinfo($this->course)->get_cm($this->instance->cmid);
        $manager = \mod_choice\manager::create_from_coursemodule($cm);
        $this->assertNotNull($manager);
    }

    /**
     * Test retrieving answers for a specific user.
     *
     * @covers \mod_choice\manager::get_answers
     */
    public function test_retrieve_answers_for_specific_user(): void {
        global $DB;
        $manager = \mod_choice\manager::create_from_instance($this->instance);
        $user = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        $choices = $DB->get_fieldset('choice_options', 'id', ['choiceid' => $this->instance->id]);
        $choice = array_shift($choices); // Remove the first choice.
        $this->getDataGenerator()->get_plugin_generator('mod_choice')->create_response([
            'choiceid' => $this->instance->id,
            'responses' => $choice,
            'userid' => $user->id,
        ]);

        $answers = $manager->get_answers($user->id);
        $this->assertCount(1, $answers);
    }

    /**
     * Test retrieving answers count for all users.
     *
     * @covers \mod_choice\manager::get_answers_count
     */
    public function test_retrieve_answers_count_for_all_users(): void {
        global $DB;
        $manager = \mod_choice\manager::create_from_instance($this->instance);
        $user1 = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        $user2 = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        $choices = $DB->get_fieldset('choice_options', 'id', ['choiceid' => $this->instance->id]);
        $choice = array_shift($choices); // Remove the first choice.
        $this->getDataGenerator()->get_plugin_generator('mod_choice')->create_response([
            'choiceid' => $this->instance->id,
            'responses' => $choice,
            'userid' => $user1->id,
        ]);
        $this->getDataGenerator()->get_plugin_generator('mod_choice')->create_response([
            'choiceid' => $this->instance->id,
            'responses' => $choice,
            'userid' => $user2->id,
        ]);

        $count = $manager->get_answers_count();
        $this->assertEquals(2, $count);
        $count = $manager->get_answers_count($user1->id);
        $this->assertEquals(1, $count);
    }
}
