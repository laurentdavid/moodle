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

/**
 * AI Placement module assist utils test.
 *
 * @package    aiplacement_modassist
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \aiplacement_modassist\utils
 * @runTestsInSeparateProcesses
 */
final class utils_test extends \advanced_testcase {

    /**
     * Test get_info_for_module method.
     */
    public function setUp(): void {
        global $CFG, $DB;
        include_once($CFG->libdir . '/upgradelib.php');
        parent::setUp();
        $this->resetAfterTest();
        $modpath = "{$CFG->dirroot}/ai/placement/modassist/tests/fixtures/fakeplugins/mod_fake";
        $this->add_mocked_plugin(
            'mod',
            'fake',
            $modpath
        );
        // Make sure class is loaded in the classmap if not this fail to load the classes in the fake module.
        $mockedcomponent = new \ReflectionClass(\core_component::class);
        $fillclassmap = $mockedcomponent->getMethod('fill_classmap_cache');
        $fillclassmap->invoke(null);
        $fillfilemap = $mockedcomponent->getMethod('fill_filemap_cache');
        $fillfilemap->invoke(null);
        ob_start();
        upgrade_noncore(false);
        upgrade_finished();
        ob_end_clean();
    }

    /**
     * Test is_mod_assist_available method.
     */
    public function test_is_mod_assist_available(): void {
        global $DB;
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $module = $this->getDataGenerator()->create_module('fake', ['course' => $course->id]);
        $context = \context_module::instance($module->cmid);
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->getDataGenerator()->enrol_user($user1->id, $course->id, 'manager');
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, 'editingteacher');

        // Provider is not enabled.
        $this->setUser($user1);
        set_config('enabled', 0, 'aiprovider_openai');
        $this->assertFalse(utils::is_mod_assist_available($context));

        set_config('enabled', 1, 'aiprovider_openai');
        set_config('apikey', '123', 'aiprovider_openai');
        set_config('orgid', 'abc', 'aiprovider_openai');

        // Plugin is not enabled.
        $this->setUser($user1);
        set_config('enabled', 0, 'aiplacement_modassist');
        $this->assertFalse(utils::is_mod_assist_available($context));

        // Plugin is enabled but user does not have capability.
        assign_capability('aiplacement/modassist:generate_text', CAP_PROHIBIT, $teacherrole->id, $context);
        $this->setUser($user2);
        set_config('enabled', 1, 'aiplacement_modassist');
        $this->assertFalse(utils::is_mod_assist_available($context));

        // Plugin is enabled, user has capability and placement action is not available.
        $this->setUser($user1);
        set_config('generate_text', 0, 'aiplacement_modassist');
        $this->assertFalse(utils::is_mod_assist_available($context));

        // Plugin is enabled, user has capability and provider action is not available.
        $this->setUser($user1);
        set_config('generate_text', 0, 'aiprovider_openai');
        set_config('generate_text', 1, 'aiplacement_modassist');
        $this->assertFalse(utils::is_mod_assist_available($context));

        // Plugin is enabled, user has capability, placement action is available and provider action is available.
        $this->setUser($user1);
        set_config('generate_text', 1, 'aiprovider_openai');
        set_config('generate_text', 1, 'aiplacement_modassist');
        $this->assertTrue(utils::is_mod_assist_available($context));
    }
}
