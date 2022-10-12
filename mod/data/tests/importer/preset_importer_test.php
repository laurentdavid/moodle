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
 * Preset importer class tests
 *
 * @package    mod_data
 * @category   test
 * @copyright  2022 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_data\importer;

use mod_data\local\importer\preset_existing_importer;
use mod_data\local\importer\preset_importer;
use mod_data\local\importer\preset_upload_importer;
use mod_data\manager;
use mod_data\preset;
defined('MOODLE_INTERNAL') || die();

/**
 * Dummy class to be able to est the do_get_import_action_from_settings protected method.
 *
 * @package    mod_data
 * @category   test
 * @copyright  2022 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class testable_preset_importer extends preset_importer {
    /**
     * Make the protected method public so we can test it.
     *
     * @param object $settings
     * @return array
     */
    public static function do_test_get_import_action_from_settings(object $settings): array {
        return self::do_get_import_action_from_settings($settings);
    }
}

/**
 * Unit tests for preset helper
 *
 * @package    mod_data
 * @category   test
 * @copyright  2022 Laurent David <laurent.david@moodle.com>
 * @copyright  2022 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \mod_data\local\importer\preset_importer
 */
class preset_importer_test extends \advanced_testcase {
    /**
     * Test for needs_mapping method.
     *
     * @covers ::needs_mapping
     */
    public function test_needs_mapping() {
        global $CFG, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course and a database activity.
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module(manager::MODULE, ['course' => $course]);
        $manager = manager::create_from_instance($activity);

        // Create presets and importers.
        $pluginname = 'imagegallery';
        $plugin = preset::create_from_plugin(null, $pluginname);
        $pluginimporter = new preset_existing_importer($manager, '/' . $pluginname);

        $plugingenerator = $this->getDataGenerator()->get_plugin_generator('mod_data');
        $record = (object) [
                'name' => 'Testing preset name',
                'description' => 'Testing preset description',
        ];
        $saved = $plugingenerator->create_preset($activity, $record);
        $savedimporter = new preset_existing_importer($manager, $USER->id . '/Testing preset name');

        $fixturepath = $CFG->dirroot . '/mod/data/tests/fixtures/image_gallery_preset.zip';

        // Create a storage file.
        $draftid = file_get_unused_draft_itemid();
        $filerecord = [
                'component' => 'user',
                'filearea' => 'draft',
                'contextid' => \context_user::instance($USER->id)->id,
                'itemid' => $draftid,
                'filename' => 'image_gallery_preset.zip',
                'filepath' => '/'
        ];
        $fs = get_file_storage();
        $file = $fs->create_file_from_pathname($filerecord, $fixturepath);
        $uploadedimporter = new preset_upload_importer($manager,
                $fs->get_file_system()->get_local_path_from_storedfile($file));

        // Needs mapping returns false for empty databases.
        $this->assertFalse($pluginimporter->needs_mapping());
        $this->assertFalse($savedimporter->needs_mapping());
        $this->assertFalse($uploadedimporter->needs_mapping());

        // Add a field to the database.
        $fieldrecord = new \stdClass();
        $fieldrecord->name = 'field1';
        $fieldrecord->type = 'text';
        $plugingenerator->create_field($fieldrecord, $activity);

        // Needs mapping returns true for non-empty databases.
        $this->assertTrue($pluginimporter->needs_mapping());
        $this->assertTrue($savedimporter->needs_mapping());
        $this->assertTrue($uploadedimporter->needs_mapping());
    }
    /**
     * Test import preset actions conversion.
     *
     * @param array $settings
     * @param array $expected
     * @dataProvider settings_provider
     * @covers \mod_data\importer\preset_importer::get_import_action_from_settings
     */
    public function test_import_action_from_settings_test(array $settings, array $expected):void {
        $result = testable_preset_importer::do_test_get_import_action_from_settings((object) $settings);
        foreach ($expected as $key => $fieldsname) {
            $this->assertEquals($fieldsname, array_keys($result[$key]), $key);
        }
    }

    /**
     * Data provider
     * @return array
     */
    public function settings_provider() {
        return [
                'all prexisting all' => [
                        'settings' => [
                                'importfields' => [
                                        (object) [
                                                'type' => 'text',
                                                'name' => 'title',
                                                'required' => '0',
                                                'dataid' => '10',
                                        ],
                                        (object) [
                                                'type' => 'text',
                                                'name' => 'caption',
                                                'required' => '0',
                                                'dataid' => '10',
                                        ],
                                ],
                                'currentfields' => [
                                        (object) [
                                                'id' => '92',
                                                'dataid' => '10',
                                                'type' => 'text',
                                                'name' => 'title',
                                                'description' => '',
                                                'required' => '0',
                                        ],
                                        (object) [
                                                'id' => '92',
                                                'dataid' => '10',
                                                'type' => 'text',
                                                'name' => 'caption',
                                                'description' => '',
                                                'required' => '0',
                                        ],
                                ]
                        ],
                        'expected' => [
                                'fieldstocreate' => [],
                                'fieldstodelete' => [],
                                'fieldstokeep' => [
                                        'title',
                                        'caption',
                                ],
                                'fieldstoupdate' => []
                        ]
                ],
                'creating all' => [
                        'settings' => [
                                'importfields' => [
                                        (object) [
                                                'type' => 'text',
                                                'name' => 'title',
                                                'required' => '0',
                                                'dataid' => '10',
                                        ],
                                        (object) [
                                                'type' => 'text',
                                                'name' => 'caption',
                                                'required' => '0',
                                                'dataid' => '10',
                                        ],
                                ],
                                'currentfields' => [
                                ]
                        ],
                        'expected' => [
                                'fieldstocreate' => [
                                        'title',
                                        'caption',
                                ],
                                'fieldstodelete' => [],
                                'fieldstokeep' => [],
                                'fieldstoupdate' => []
                        ]
                ],
                'deleting and updating' => [
                        'settings' => [
                                'importfields' => [
                                        (object) [
                                                'type' => 'text',
                                                'name' => 'title',
                                                'required' => '1',
                                                'dataid' => '10',
                                        ],
                                        (object) [
                                                'type' => 'text',
                                                'name' => 'caption',
                                                'required' => '0',
                                                'dataid' => '10',
                                        ],
                                ],
                                'currentfields' => [
                                        (object) [
                                                'id' => '92',
                                                'dataid' => '10',
                                                'type' => 'text',
                                                'name' => 'title',
                                                'description' => '',
                                                'required' => '0',
                                        ],
                                        (object) [
                                                'id' => '92',
                                                'dataid' => '10',
                                                'type' => 'text',
                                                'name' => 'caption1',
                                                'description' => '',
                                                'required' => '0',
                                        ],
                                ]
                        ],
                        'expected' => [
                                'fieldstocreate' => [
                                        'caption',
                                ],
                                'fieldstodelete' => [
                                        'caption1'
                                ],
                                'fieldstokeep' => [],
                                'fieldstoupdate' => [
                                        'title'
                                ]
                        ]
                ]
        ];
    }
}
