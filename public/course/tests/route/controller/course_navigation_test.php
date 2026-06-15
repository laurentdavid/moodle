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
use core\url;
use core_course\modinfo;
use core_courseformat\formatactions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Course navigation controller tests.
 *
 * @package     core_course
 * @copyright   2025 Laurent David <laurent.david@moodle.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
#[CoversClass(\core_course\route\controller\course_navigation::class)]
final class course_navigation_test extends route_testcase {
    /**
     * Test the course navigation course module next route.
     *
     * @param array $cmsdef
     * @param string $current
     * @param array $expected
     * @param string $role
     * @param array $sectionsdef
     */
    #[DataProvider('cm_next_provider')]
    public function test_cm_next(
        array $cmsdef,
        string $current,
        array $expected,
        string $role = 'student',
        array $sectionsdef = [],
    ): void {
        $this->execute_cm_navigation_test(
            cmsdef: $cmsdef,
            current: $current,
            expected: $expected,
            role: $role,
            direction: 'next',
            sectionsdef: $sectionsdef,
        );
    }

    /**
     * Data provider for test_cm_next.
     *
     * @return \Generator
     */
    public static function cm_next_provider(): \Generator {
        global $CFG;
        require_once("$CFG->libdir/resourcelib.php");

        $emailavailability = '{"op":"&","c":[{"type":"profile","sf":"email","op":"isequalto","v":"';
        yield 'Simple case (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
            ],
            'role' => 'teacher',
        ];
        yield 'Simple case (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
            ],
        ];
        yield 'Hidden module (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'options' => ['visible' => false]],
                ['name' => 'cm3'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2', // Teachers can see hidden modules.
            ],
            'role' => 'teacher',
        ];
        yield 'Hidden module (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'options' => ['visible' => false]],
                ['name' => 'cm3'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm3', // Students cannot see hidden modules.
            ],
        ];
        yield 'Stealth module (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'options' => ['visibleoncoursepage' => false]],
                ['name' => 'cm3'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2', // Teachers can see stealth modules in the course page.
            ],
            'role' => 'editingteacher',
        ];
        yield 'Stealth module (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'options' => ['visibleoncoursepage' => false]],
                ['name' => 'cm3'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm3', // Students cannot see stealth modules in the course page.
            ],
        ];
        yield 'Hidden last module (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'cm2', 'options' => ['section' => 2, 'visible' => false]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
            ],
            'role' => 'teacher',
        ];
        yield 'Hidden last module (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'cm2', 'options' => ['section' => 2, 'visible' => false]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'course', // Students cannot see hidden modules.
            ],
        ];
        yield 'Stealth last module (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'cm2', 'options' => ['section' => 2, 'visibleoncoursepage' => false]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
            ],
            'role' => 'teacher',
        ];
        yield 'Stealth last module (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'cm2', 'options' => ['section' => 2, 'visibleoncoursepage' => false]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'course', // Students cannot see stealth modules in the course page.
            ],
        ];
        yield 'Restricted module visible (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                [
                    'name' => 'cm2',
                    'options' => ['availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[true]}'],
                ],
                ['name' => 'cm3'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2', // Teachers can always see restricted modules.
            ],
            'role' => 'editingteacher',
        ];
        yield 'Restricted module visible (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                [
                    'name' => 'cm2',
                    'options' => ['availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[true]}'],
                ],
                ['name' => 'cm3'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2', // Students can see restricted modules when the restrictions are visible.
            ],
        ];
        yield 'Restricted module hidden (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                [
                    'name' => 'cm2',
                    'options' => ['availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[false]}'],
                ],
                ['name' => 'cm3'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2', // Teachers can always see restricted modules.
            ],
            'role' => 'editingteacher',
        ];
        yield 'Restricted module hidden (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                [
                    'name' => 'cm2',
                    'options' => ['availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[false]}'],
                ],
                ['name' => 'cm3'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm3', // Students cannot see restricted modules when the restrictions are hidden.
            ],
        ];
        yield 'Restricted module hidden when user meets the restriction (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                [
                    'name' => 'cm2',
                    'options' => ['availability' => $emailavailability . 'student@moodle.invalid"}],"showc":[false]}'],
                ],
                ['name' => 'cm3'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2', // Students can see restricted modules when they meet the restriction.
            ],
        ];
        yield 'Subsection: With next module being a subsection (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => ['section' => 2]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
            ],
        ];
        yield 'Subsection: With next module being a subsection in the last section (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'course',
            ],
        ];
        yield 'Subsection: With next module being a label and subsections (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'label'],
                ['name' => 'subsection1', 'type' => 'subsection'],
                ['name' => 'cm3', 'options' => ['section' => 'subsection1']],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm3',
            ],
        ];
        yield 'Subsection: With next module being in a hidden subsection (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => ['section' => 2, 'visibility' => false]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm3', // Students cannot see cm2 because it's in a hidden subsection.
            ],
        ];
        yield 'Subsection: With next module being in a hidden subsection (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => ['section' => 2, 'visibility' => false]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2', // Teachers can see modules in hidden subsections.
            ],
            'role' => 'editingteacher',
        ];
        yield 'Subsection: With next module being in a restricted public subsection (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => [
                    'section' => 2,
                    'availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[true]}',
                ]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm3', // Students cannot see modules in a restricted subsection if the restrictions are not met.
            ],
        ];
        yield 'Subsection: With next module being in a restricted public subsection (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => [
                    'section' => 2,
                    'availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[true]}',
                ]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2', // Teachers can see modules in a restricted subsection even if they don't meet the restriction.
            ],
            'role' => 'editingteacher',
        ];
        yield 'Subsection: With next module being in a restricted hidden subsection (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => [
                    'section' => 2,
                    'availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[false]}',
                ]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm3', // Students cannot see modules in a restricted subsection if the restrictions are not met.
            ],
        ];
        yield 'Subsection: With next module being in a restricted hidden subsection (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => [
                    'section' => 2,
                    'availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[false]}',
                ]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2', // Teachers can see modules in a restricted subsection even if they don't meet the restriction.
            ],
            'role' => 'editingteacher',
        ];
        yield 'Subsection: With next module being in a restricted public subsection when user meets the restrictions (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => [
                    'section' => 2,
                    'availability' => $emailavailability . 'student@moodle.invalid"}],"showc":[true]}',
                ]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
            ],
        ];
        yield 'Subsection: With next module being in a restricted hidden subsection when user meets the restrictions (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => [
                    'section' => 2,
                    'availability' => $emailavailability . 'student@moodle.invalid"}],"showc":[false]}',
                ]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
            ],
        ];
        yield 'Sections - Simple case (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'section',
                'id' => '2',
            ],
            'role' => 'teacher',
        ];
        yield 'Sections - Simple case (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'section',
                'id' => '2',
            ],
        ];
        yield 'Sections - Hidden section (student)' => [
            'cmsdef' => [
                ['name' => 'cm0', 'options' => ['section' => 0]],
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm0',
            'expected' => [
                'type' => 'section',
                'id' => '2', // Students cannot see the hidden section, so the next one should be the one after.
            ],
            'sectionsdef' => [
                ['section' => 1, 'hidden' => true],
            ],
        ];
        yield 'Sections - Hidden section (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm0', 'options' => ['section' => 0]],
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm0',
            'expected' => [
                'type' => 'section',
                'id' => '2', // Non-editing teachers cannot see the hidden section, so the next one should be the one after.
            ],
            'role' => 'teacher',
            'sectionsdef' => [
                ['section' => 1, 'hidden' => true],
            ],
        ];
        yield 'Sections - Hidden section (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm0', 'options' => ['section' => 0]],
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm0',
            'expected' => [
                'type' => 'section',
                'id' => '1', // Teachers can see the hidden section.
            ],
            'role' => 'editingteacher',
            'sectionsdef' => [
                ['section' => 1, 'hidden' => true],
            ],
        ];
        yield 'Sections - With last module in a hidden section (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'course', // As the next section is hidden, we should redirect to course page.
            ],
            'sectionsdef' => [
                ['section' => 2, 'hidden' => true],
            ],
        ];
        yield 'Sections - With last module in a hidden section (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'section', // Editing teachers can see the hidden section.
                'id' => '2',
            ],
            'role' => 'editingteacher',
            'sectionsdef' => [
                ['section' => 2, 'hidden' => true],
            ],
        ];
        yield 'Sections - Empty section (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'section',
                'id' => '2',
            ],
        ];
        yield 'Restricted section visible - Simple case (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'section', // Editing teachers can see the restricted section.
                'id' => '2',
            ],
            'role' => 'editingteacher',
            'sectionsdef' => [
                ['section' => 2, 'available' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[true]}'],
            ],
        ];
        yield 'Restricted section visible - Simple case (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'section',
                'id' => '2',
            ],
            'sectionsdef' => [
                ['section' => 2, 'available' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[true]}'],
            ],
        ];
        yield 'Restricted section hidden - Simple case (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'section', // Editing teachers can see the restricted section.
                'id' => '2',
            ],
            'role' => 'editingteacher',
            'sectionsdef' => [
                ['section' => 2, 'available' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[false]}'],
            ],
        ];
        yield 'Restricted section hidden - Simple case (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'course', // Students cannot see the restricted section.
            ],
            'sectionsdef' => [
                ['section' => 2, 'available' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[false]}'],
            ],
        ];
        yield 'Restricted section hidden - Simple case when user meets the restriction (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'section', // Student meets the restriction, so the section should be visible.
                'id' => '2',
            ],
            'sectionsdef' => [
                ['section' => 2, 'available' => $emailavailability . 'student@moodle.invalid"}],"showc":[false]}'],
            ],
        ];
        yield 'Resource: Display auto (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'resource', 'options' => ['display' => RESOURCELIB_DISPLAY_AUTO]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'Resource: Display embed (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'resource', 'options' => ['display' => RESOURCELIB_DISPLAY_EMBED]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'Resource: Display frame (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'resource', 'options' => ['display' => RESOURCELIB_DISPLAY_FRAME]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'Resource: Display new (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'resource', 'options' => ['display' => RESOURCELIB_DISPLAY_NEW]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'Resource: Display download (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'resource', 'options' => ['display' => RESOURCELIB_DISPLAY_DOWNLOAD]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'Resource: Display open (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'resource', 'options' => ['display' => RESOURCELIB_DISPLAY_OPEN]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'Resource: Display popup (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'resource', 'options' => [
                    'display' => RESOURCELIB_DISPLAY_POPUP,
                    'popupwidth' => 800,
                    'popupheight' => 600,
                ]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'URL: Display auto (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'url', 'options' => ['display' => RESOURCELIB_DISPLAY_AUTO]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'URL: Display embed (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'url', 'options' => ['display' => RESOURCELIB_DISPLAY_EMBED]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'URL: Display frame (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'url', 'options' => ['display' => RESOURCELIB_DISPLAY_FRAME]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'URL: Display new (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'url', 'options' => ['display' => RESOURCELIB_DISPLAY_NEW]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'URL: Display open (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'url', 'options' => ['display' => RESOURCELIB_DISPLAY_OPEN]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'URL: Display popup (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'url', 'options' => [
                    'display' => RESOURCELIB_DISPLAY_POPUP,
                    'popupwidth' => 800,
                    'popupheight' => 600,
                ]],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm2',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'With module not supporting FEATURE_CAN_DISPLAY (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'qbank'],
                ['name' => 'cm3'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm3', // The cm2 should be skipped as it does not support FEATURE_CAN_DISPLAY.
            ],
        ];
        yield 'With module not supporting FEATURE_CAN_DISPLAY (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'qbank'],
                ['name' => 'cm3'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm3', // The cm2 should be skipped as it does not support FEATURE_CAN_DISPLAY.
            ],
            'role' => 'teacher',
        ];
        yield 'With module not supporting FEATURE_CAN_DISPLAY (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'qbank'],
                ['name' => 'cm3'],
            ],
            'current' => 'cm1',
            'expected' => [
                'id' => 'cm3', // The cm2 should be skipped as it does not support FEATURE_CAN_DISPLAY.
            ],
            'role' => 'editingteacher',
        ];
        yield 'Last activity of a section (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'options' => ['visible' => false]],
            ],
            'current' => 'cm2',
            'expected' => [
                'type' => 'section',
                'id' => '1',
            ],
        ];
        yield 'Last activity of a course (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'course',
            ],
        ];
        yield 'With last module without url in the first section (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 0]],
                ['name' => 'cm2', 'type' => 'label', 'options' => ['section' => 0]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'section',
                'id' => '1',
            ],
        ];
        yield 'With last module without url in the last section (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'cm2', 'type' => 'label', 'options' => ['section' => 2]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'course',
            ],
        ];
        yield 'With module that does not exist (student)' => [
            'cmsdef' => [
                ['name' => 'cm0'],
                ['name' => 'cm1'],
            ],
            'current' => 'cmthatdoesnotexist',
            'expected' => [
                'type' => 'error',
                'statuscode' => 404,
            ],
        ];
    }

    /**
     * Test the course navigation course module previous route.
     *
     * @param array $cmsdef
     * @param string $current
     * @param array $expected
     * @param string $role
     * @param array $sectionsdef
     */
    #[DataProvider('cm_previous_provider')]
    public function test_cm_previous(
        array $cmsdef,
        string $current,
        array $expected,
        string $role = 'student',
        array $sectionsdef = [],
    ): void {
        $this->execute_cm_navigation_test(
            cmsdef: $cmsdef,
            current: $current,
            expected: $expected,
            role: $role,
            direction: 'previous',
            sectionsdef: $sectionsdef,
        );
    }

    /**
     * Data provider for test_cm_previous.
     *
     * @return \Generator
     */
    public static function cm_previous_provider(): \Generator {
        global $CFG;
        require_once("$CFG->libdir/resourcelib.php");

        $emailavailability = '{"op":"&","c":[{"type":"profile","sf":"email","op":"isequalto","v":"';
        yield 'Simple case (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
            ],
            'role' => 'teacher',
        ];
        yield 'Simple case (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
            ],
        ];
        yield 'Hidden module (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'options' => ['visible' => false]],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm2', // Teachers can see hidden modules.
            ],
            'role' => 'teacher',
        ];
        yield 'Hidden module (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'options' => ['visible' => false]],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm1', // Students cannot see hidden modules.
            ],
        ];
        yield 'Stealth module (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'options' => ['visibleoncoursepage' => false]],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm2', // Teachers can see stealth modules in the course page.
            ],
            'role' => 'teacher',
        ];
        yield 'Stealth module (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'options' => ['visibleoncoursepage' => false]],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm1', // Students cannot see stealth modules in the course page.
            ],
        ];
        yield 'Hidden first module (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['visible' => false]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1', // Students cannot see stealth modules in the course page.
            ],
            'role' => 'teacher',
        ];
        yield 'Hidden first module (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['visible' => false]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'type' => 'course', // Students cannot see hidden modules.
            ],
        ];
        yield 'Stealth first module (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['visibleoncoursepage' => false]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
            ],
            'role' => 'teacher',
        ];
        yield 'Stealth first module (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['visibleoncoursepage' => false]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'type' => 'course', // Students cannot see stealth modules in the course page.
            ],
        ];
        yield 'Restricted module visible (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                [
                    'name' => 'cm2',
                    'options' => ['availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[true]}'],
                ],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm2', // Teachers can always see restricted modules.
            ],
            'role' => 'editingteacher',
        ];
        yield 'Restricted module visible (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                [
                    'name' => 'cm2',
                    'options' => ['availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[true]}'],
                ],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm2', // Students can see restricted modules when the restrictions are visible.
            ],
        ];
        yield 'Restricted module hidden (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                [
                    'name' => 'cm2',
                    'options' => ['availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[false]}'],
                ],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm2', // Teachers can always see restricted modules.
            ],
            'role' => 'editingteacher',
        ];
        yield 'Restricted module hidden (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                [
                    'name' => 'cm2',
                    'options' => ['availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[false]}'],
                ],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm1', // Students cannot see restricted modules when the restrictions are hidden.
            ],
        ];
        yield 'Restricted module hidden when user meets the restriction (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                [
                    'name' => 'cm2',
                    'options' => ['availability' => $emailavailability . 'student@moodle.invalid"}],"showc":[false]}'],
                ],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm2', // Students can see restricted modules when they meet the restriction.
            ],
        ];
        yield 'Subsection: With previous module being a subsection (student)' => [
            'cmsdef' => [
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => ['section' => 2]],
                ['name' => 'cm1', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
            ],
        ];
        yield 'Subsection: With previous module being a subsection in the first section (student)' => [
            'cmsdef' => [
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => ['section' => 0]],
                ['name' => 'cm1', 'options' => ['section' => 'subsection1']],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'course',
            ],
        ];
        yield 'Subsection: With previous module outside a subsection (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => ['section' => 2]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
            ],
        ];
        yield 'Subsection: With a subsection with only one module (student)' => [
            'cmsdef' => [
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => ['section' => 2]],
                ['name' => 'cm1', 'options' => ['section' => 'subsection1']],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'section',
                'id' => '2',
            ],
        ];
        yield 'Subsection: With previous module being a label and subsections (student)' => [
            'cmsdef' => [
                ['name' => 'subsection1', 'type' => 'subsection'],
                ['name' => 'cm1', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm2', 'type' => 'label'],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm1',
            ],
        ];
        yield 'Subsection: With previous module being in a hidden subsection (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => ['section' => 2, 'visibility' => false]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm1', // Students cannot see cm2 because it's in a hidden subsection.
            ],
        ];
        yield 'Subsection: With previous module being in a hidden subsection (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => ['section' => 2, 'visibility' => false]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm2', // Teachers can see modules in hidden subsections.
            ],
            'role' => 'editingteacher',
        ];
        yield 'Subsection: With previous module being in a restricted public subsection (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => [
                    'section' => 2,
                    'availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[true]}',
                ]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm1', // Students cannot see modules in a restricted subsection if the restrictions are not met.
            ],
        ];
        yield 'Subsection: With previous module being in a restricted public subsection (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => [
                    'section' => 2,
                    'availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[true]}',
                ]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm2', // Teachers can see modules in a restricted subsection even if they don't meet the restriction.
            ],
            'role' => 'editingteacher',
        ];
        yield 'Subsection: With previous module being in a restricted hidden subsection (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => [
                    'section' => 2,
                    'availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[false]}',
                ]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm1', // Students cannot see modules in a restricted subsection if the restrictions are not met.
            ],
        ];
        yield 'Subsection: With previous module being in a restricted hidden subsection (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => [
                    'section' => 2,
                    'availability' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[false]}',
                ]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm2', // Teachers can see modules in a restricted subsection even if they don't meet the restriction.
            ],
            'role' => 'editingteacher',
        ];
        yield 'Subsection: With previous module being in a restricted public subsection when user meets restrictions (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => [
                    'section' => 2,
                    'availability' => $emailavailability . 'student@moodle.invalid"}],"showc":[true]}',
                ]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm2',
            ],
        ];
        yield 'Subsection: With previous module being in a restricted hidden subsection when user meets restrictions (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 2]],
                ['name' => 'subsection1', 'type' => 'subsection', 'options' => [
                    'section' => 2,
                    'availability' => $emailavailability . 'student@moodle.invalid"}],"showc":[false]}',
                ]],
                ['name' => 'cm2', 'options' => ['section' => 'subsection1']],
                ['name' => 'cm3', 'options' => ['section' => 2]],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm2',
            ],
        ];
        yield 'Sections - Simple case (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm2',
            'expected' => [
                'type' => 'section',
                'id' => '2',
            ],
            'role' => 'teacher',
        ];
        yield 'Sections - Simple case (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm2',
            'expected' => [
                'type' => 'section',
                'id' => '2',
            ],
        ];
        yield 'Sections - Hidden section (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm2',
            'expected' => [
                'type' => 'section',
                'id' => '2',
            ],
            'sectionsdef' => [
                ['section' => 1, 'hidden' => true],
            ],
        ];
        yield 'Sections - Hidden section (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm2',
            'expected' => [
                'type' => 'section',
                'id' => '2',
            ],
            'role' => 'editingteacher',
            'sectionsdef' => [
                ['section' => 1, 'hidden' => true],
            ],
        ];
        yield 'Sections - With module in a hidden section (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm2',
            'expected' => [
                'type' => 'section',
                'id' => '2', // Teachers can see the hidden section.
            ],
            'role' => 'editingteacher',
            'sectionsdef' => [
                ['section' => 2, 'hidden' => true],
            ],
        ];
        yield 'Restricted section visible - Simple case (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm2',
            'expected' => [
                'type' => 'section', // Editing teachers can see the restricted section.
                'id' => '2',
            ],
            'role' => 'editingteacher',
            'sectionsdef' => [
                ['section' => 2, 'available' => $emailavailability . 'nomail@moodle.invalid"}],"showc":[true]}'],
            ],
        ];
        yield 'Restricted section hidden - Simple case when user meets the restriction (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm2',
            'expected' => [
                'type' => 'section', // Student meets the restriction, so the section should be visible.
                'id' => '2',
            ],
            'sectionsdef' => [
                ['section' => 2, 'available' => $emailavailability . 'student@moodle.invalid"}],"showc":[false]}'],
            ],
        ];
        yield 'Resource: Display auto (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'resource', 'options' => ['display' => RESOURCELIB_DISPLAY_AUTO]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'Resource: Display embed (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'resource', 'options' => ['display' => RESOURCELIB_DISPLAY_EMBED]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'Resource: Display frame (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'resource', 'options' => ['display' => RESOURCELIB_DISPLAY_FRAME]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'Resource: Display new (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'resource', 'options' => ['display' => RESOURCELIB_DISPLAY_NEW]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'Resource: Display download (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'resource', 'options' => ['display' => RESOURCELIB_DISPLAY_DOWNLOAD]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'Resource: Display open (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'resource', 'options' => ['display' => RESOURCELIB_DISPLAY_OPEN]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'Resource: Display popup (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'resource', 'options' => [
                    'display' => RESOURCELIB_DISPLAY_POPUP,
                    'popupwidth' => 800,
                    'popupheight' => 600,
                ]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'URL: Display auto (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'url', 'options' => ['display' => RESOURCELIB_DISPLAY_AUTO]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'URL: Display embed (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'url', 'options' => ['display' => RESOURCELIB_DISPLAY_EMBED]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'URL: Display frame (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'url', 'options' => ['display' => RESOURCELIB_DISPLAY_FRAME]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'URL: Display new (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'url', 'options' => ['display' => RESOURCELIB_DISPLAY_NEW]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'URL: Display open (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'url', 'options' => ['display' => RESOURCELIB_DISPLAY_OPEN]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'URL: Display popup (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'url', 'options' => [
                    'display' => RESOURCELIB_DISPLAY_POPUP,
                    'popupwidth' => 800,
                    'popupheight' => 600,
                ]],
                ['name' => 'cm2'],
            ],
            'current' => 'cm2',
            'expected' => [
                'id' => 'cm1',
                'params' => ['id', 'forceview'],
            ],
        ];
        yield 'With module not supporting FEATURE_CAN_DISPLAY (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'qbank'],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm1', // The cm2 should be skipped as it does not support FEATURE_CAN_DISPLAY.
            ],
        ];
        yield 'With module not supporting FEATURE_CAN_DISPLAY (teacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'qbank'],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm1', // The cm2 should be skipped as it does not support FEATURE_CAN_DISPLAY.
            ],
            'role' => 'teacher',
        ];
        yield 'With module not supporting FEATURE_CAN_DISPLAY (editingteacher)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2', 'type' => 'qbank'],
                ['name' => 'cm3'],
            ],
            'current' => 'cm3',
            'expected' => [
                'id' => 'cm1', // The cm2 should be skipped as it does not support FEATURE_CAN_DISPLAY.
            ],
            'role' => 'editingteacher',
        ];
        yield 'First activity of a course (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2'],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'course',
            ],
        ];
        yield 'First activity of a section (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'options' => ['section' => 1]],
                ['name' => 'cm2', 'options' => ['section' => 1]],
            ],
            'current' => 'cm1',
            'expected' => [
                'type' => 'section',
                'id' => '1',
            ],
        ];
        yield 'With first module without url in the first section (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'label'],
                ['name' => 'cm2', 'options' => ['section' => 0]],
            ],
            'current' => 'cm2',
            'expected' => [
                'type' => 'course',
            ],
        ];
        yield 'With first module without url in the last section (student)' => [
            'cmsdef' => [
                ['name' => 'cm1', 'type' => 'label', 'options' => ['section' => 2]],
                ['name' => 'cm2', 'options' => ['section' => 2]],
            ],
            'current' => 'cm2',
            'expected' => [
                'type' => 'section',
                'id' => '2',
            ],
        ];
        yield 'With module that does not exist (student)' => [
            'cmsdef' => [
                ['name' => 'cm1'],
                ['name' => 'cm2'],
            ],
            'current' => 'cmthatdoesnotexist',
            'expected' => [
                'type' => 'error',
                'statuscode' => 404,
            ],
        ];
    }

    /**
     * Internal helper to test navigation routes (previous / next).
     *
     * @param array $cmsdef
     * @param string $current
     * @param array $expected
     * @param string $role
     * @param string $direction
     * @param int $numsections
     * @param array $sectionsdef
     */
    protected function execute_cm_navigation_test(
        array $cmsdef,
        string $current,
        array $expected,
        string $role = 'student',
        string $direction = 'next',
        int $numsections = 2,
        array $sectionsdef = [],
    ): void {
        $this->resetAfterTest();
        set_config('allowstealth', 1);
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['numsections' => $numsections]);
        foreach ($sectionsdef as $section) {
            if (isset($section['hidden'])) {
                $sectioninfo = get_fast_modinfo($course)->get_section_info($section['section']);
                \core_courseformat\formatactions::section($course)->update($sectioninfo, ['visible' => !$section['hidden']]);
            } else if (isset($section['available'])) {
                $sectioninfo = get_fast_modinfo($course)->get_section_info($section['section']);
                $availability = $section['available'];
                \core_courseformat\formatactions::section($course)->update($sectioninfo, ['availability' => $availability]);
            }
        }
        $user = $generator->create_and_enrol($course, $role, ['email' => $role . '@moodle.invalid']);
        $cms = [];
        $hiddensubsections = [];
        foreach ($cmsdef as $cmdef) {
            $cms[$cmdef['name']] = $this->create_module_or_subsection(
                courseid: $course->id,
                key: $cmdef['name'],
                type: $cmdef['type'] ?? 'assign',
                options: $cmdef['options'] ?? [],
            );
            // Mark the subsection as hidden, to change the visibility later, once all the course modules are created.
            if (
                isset($cmdef['type']) && $cmdef['type'] === 'subsection'
                && isset($cmdef['options']['visibility']) && $cmdef['options']['visibility'] === false
            ) {
                $hiddensubsections[] = $cms[$cmdef['name']];
            }
        }

        // If there are hidden subsections, call the API method to set the visibility of the course modules inside them too.
        foreach ($hiddensubsections as $hiddensubsection) {
            $sectioninfo = get_fast_modinfo($course->id)->get_section_info_by_component('mod_subsection', $hiddensubsection->id);
            formatactions::section($course->id)->set_visibility($sectioninfo, false);
        }

        $cmid = $cms[$current]->cmid ?? 9999; // If we cannot find it we will test the error case of not found.

        $this->setUser($user);
        $response = $this->process_request(
            'GET',
            "course/cms/{$cmid}/{$direction}",
            route_loader_interface::ROUTE_GROUP_PAGE
        );
        if (array_key_exists('type', $expected) && $expected['type'] === 'error') {
            $this->assertEquals(
                $expected['statuscode'],
                $response->getStatusCode(),
            );
            return;
        }
        $this->assert_valid_response($response, 302);
        $location = $response->getHeader('Location'); // Just to consume the header if any.

        $this->assertNotEmpty($location, 'The redirection header should be present.');
        $this->assert_redirected_url(
            $expected['type'] ?? 'cm',
            $expected['id'] ?? '',
            $course->id,
            $location[0],
            $expected['params'] ?? [],
        );
    }

    /**
     * Helper function to assert that the redirection URL matches the expected element.
     *
     * @param string $elementtype
     * @param string $elementid
     * @param int $courseid
     * @param string $location
     * @param array $expectedparams
     */
    protected function assert_redirected_url(
        string $elementtype,
        string $elementid,
        int $courseid,
        string $location,
        array $expectedparams = [],
    ): void {
        $coursemodinfo = modinfo::instance($courseid);
        $navigationurl = null;
        switch ($elementtype) {
            case 'cm':
                $cms = $coursemodinfo->get_cms();
                $cm = null;
                foreach ($cms as $activitycm) {
                    if ($activitycm->get_name() == $elementid) {
                        $cm = $activitycm;
                        break;
                    }
                }
                $this->assertNotEmpty($cm, "The course module with name {$elementid} should be found.");
                $navigationurl = $cm->navigationurl;
                break;
            case 'section':
                $sectioninfo = $coursemodinfo->get_section_info($elementid);
                $navigationurl = course_get_url($courseid, $sectioninfo, ['navigation' => true]);
                break;
            case 'course':
                $navigationurl = course_get_url($courseid);
                break;
            default:
                $this->fail('Unknown expected element type ' . $elementtype);
        }
        $this->assertEquals(
            $navigationurl,
            new url($location),
        );
        // Check for expected parameters in the redirection URL (only when specified).
        if (!empty($expectedparams)) {
            $actualparams = array_keys((new url($location))->params());
            sort($actualparams);
            sort($expectedparams);
            $this->assertEquals(
                $expectedparams,
                $actualparams,
                "The URL parameter names do not match.\n" .
                "Expected: " . implode(', ', $expectedparams) . "\n" .
                "Actual:   " . implode(', ', $actualparams),
            );
        }
    }

    /**
     * Helper to create a course module or a subsection based on the given definition.
     * This will handle the case where we want to point to a section by name instead of id for easier test writing.
     *
     * @param int $courseid
     * @param string $key The name/key of the module
     * @param string $type The module type (default: 'assign')
     * @param array $options Additional options for the module
     * @return \stdClass The newly created course module.
     */
    protected function create_module_or_subsection(
        int $courseid,
        string $key,
        string $type = 'assign',
        array $options = [],
    ): \stdClass {
        $generator = $this->getDataGenerator();
        $moduleoptions = [
            'course' => $courseid,
            'name' => $key,
        ];
        if (isset($options['section']) && !is_int($options['section'])) {
            // We are pointing to a subsection module.
            $modinfo = modinfo::instance($courseid);
            $delegatedcms = $modinfo->get_sections_delegated_by_cm();
            foreach ($delegatedcms as $cminfosection) {
                if ($cminfosection->name == $options['section']) {
                    $options['section'] = $cminfosection->sectionnum;
                    break;
                }
            }
        }
        $moduleoptions = array_merge($moduleoptions, $options);
        return $generator->create_module($type, $moduleoptions);
    }

    /**
     * Test get_adjacent_section with next direction.
     */
    public function test_get_adjacent_section_next(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['numsections' => 3]);
        $modinfo = get_fast_modinfo($course);
        $section = $modinfo->get_section_info(0);
        $navigation = new course_navigation();

        $adjacentsection = $navigation->get_adjacent_section($modinfo, $section, 'next');
        $this->assertNotNull($adjacentsection);
        $this->assertEquals(1, $adjacentsection->section);

        $adjacentsection = $navigation->get_adjacent_section($modinfo, $adjacentsection, 'next');
        $this->assertNotNull($adjacentsection);
        $this->assertEquals(2, $adjacentsection->section);

        $adjacentsection = $navigation->get_adjacent_section($modinfo, $adjacentsection, 'next');
        $this->assertNotNull($adjacentsection);
        $this->assertEquals(3, $adjacentsection->section);

        $adjacentsection = $navigation->get_adjacent_section($modinfo, $adjacentsection, 'next');
        $this->assertNull($adjacentsection);
    }

    /**
     * Test get_adjacent_section with previous direction.
     */
    public function test_get_adjacent_section_previous(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['numsections' => 3]);
        $modinfo = get_fast_modinfo($course);
        $section = $modinfo->get_section_info(3);
        $navigation = new course_navigation();

        $adjacentsection = $navigation->get_adjacent_section($modinfo, $section, 'previous');
        $this->assertNotNull($adjacentsection);
        $this->assertEquals(2, $adjacentsection->section);

        $adjacentsection = $navigation->get_adjacent_section($modinfo, $adjacentsection, 'previous');
        $this->assertNotNull($adjacentsection);
        $this->assertEquals(1, $adjacentsection->section);

        $adjacentsection = $navigation->get_adjacent_section($modinfo, $adjacentsection, 'previous');
        $this->assertNotNull($adjacentsection);
        $this->assertEquals(0, $adjacentsection->section);

        $adjacentsection = $navigation->get_adjacent_section($modinfo, $adjacentsection, 'previous');
        $this->assertNull($adjacentsection);
    }

    /**
     * Test get_all_section_cms returns all course modules of a section.
     */
    public function test_get_all_section_cms(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['numsections' => 1]);
        $cm1 = $generator->create_module('assign', ['course' => $course->id, 'section' => 1]);
        $cm2 = $generator->create_module('assign', ['course' => $course->id, 'section' => 1]);

        $modinfo = get_fast_modinfo($course);
        $section = $modinfo->get_section_info(1);

        $navigation = new course_navigation();
        // Test that the method is public and callable
        $cms = $navigation->get_all_section_cms($modinfo, $section);

        $this->assertIsArray($cms);
        $this->assertCount(2, $cms);
    }

    /**
     * Test is_first_element in course with content.
     */
    public function test_is_first_element(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['numsections' => 1]);
        $cm1 = $generator->create_module('assign', ['course' => $course->id, 'section' => 0]);
        $cm2 = $generator->create_module('assign', ['course' => $course->id, 'section' => 0]);
        $cm3 = $generator->create_module('assign', ['course' => $course->id, 'section' => 1]);

        // Create a student user
        $student = $generator->create_and_enrol($course, 'student');
        $this->setUser($student);

        $modinfo = get_fast_modinfo($course);
        $section = $modinfo->get_section_info(0);
        $navigation = new course_navigation();
        $allcms = $navigation->get_all_section_cms($modinfo, $section);

        $cminfo = $modinfo->get_cm($cm1->cmid);
        $this->assertTrue($navigation->is_first_element($cminfo, $modinfo, $allcms));

        $cminfo = $modinfo->get_cm($cm2->cmid);
        $this->assertFalse($navigation->is_first_element($cminfo, $modinfo, $allcms));

        $section = $modinfo->get_section_info(1);
        $allcms = $navigation->get_all_section_cms($modinfo, $section);
        $cminfo = $modinfo->get_cm($cm3->cmid);
        $this->assertFalse($navigation->is_first_element($cminfo, $modinfo, $allcms));
    }

    /**
     * Test is_first_element skips non-navigable modules in section 0.
     */
    public function test_is_first_element_skips_non_navigable(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['numsections' => 2]);
        $label = $generator->create_module('label', ['course' => $course->id, 'section' => 0]);
        $hiddencm = $generator->create_module('assign', [
            'course' => $course->id,
            'section' => 0,
            'visible' => false,
        ]);
        $availability = '{"op":"&","c":[{"type":"date","d":">=","t":' . (time() + (2 * DAYSECS)) . '}],"showc":[false]}';
        $restrictedcm = $generator->create_module('assign', [
            'course' => $course->id,
            'section' => 0,
            'availability' => $availability,
        ]);
        
        $cm = $generator->create_module('assign', ['course' => $course->id, 'section' => 0]);

        // Create a student user
        $student = $generator->create_and_enrol($course, 'student');
        $this->setUser($student);

        $modinfo = get_fast_modinfo($course);
        $section = $modinfo->get_section_info(0);
        $cminfo = $modinfo->get_cm($cm->cmid);
        $navigation = new course_navigation();
        $allcms = $navigation->get_all_section_cms($modinfo, $section);

        $this->assertTrue($navigation->is_first_element($cminfo, $modinfo, $allcms));
    }

    /**
     * Test is_last_element in course with content.
     */
    public function test_is_last_element(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['numsections' => 1]);
        $cm1 = $generator->create_module('assign', ['course' => $course->id, 'section' => 0]);
        $cm2 = $generator->create_module('assign', ['course' => $course->id, 'section' => 1]);
        $cm3 = $generator->create_module('assign', ['course' => $course->id, 'section' => 1]);

        $student = $generator->create_and_enrol($course, 'student');
        $this->setUser($student);

        $modinfo = get_fast_modinfo($course);
        $section = $modinfo->get_section_info(0);
        $navigation = new course_navigation();
        $allcms = $navigation->get_all_section_cms($modinfo, $section);

        $cminfo = $modinfo->get_cm($cm1->cmid);
        $this->assertFalse($navigation->is_last_element($cminfo, $modinfo, $allcms));

        $section = $modinfo->get_section_info(1);
        $allcms = $navigation->get_all_section_cms($modinfo, $section);
        $cminfo = $modinfo->get_cm($cm2->cmid);
        $this->assertFalse($navigation->is_last_element($cminfo, $modinfo, $allcms));

        $cminfo = $modinfo->get_cm($cm3->cmid);
        $this->assertTrue($navigation->is_last_element($cminfo, $modinfo, $allcms));
    }

    /**
     * Test is_last_element skips non-navigable modules in last section.
     */
    public function test_is_last_element_skips_non_navigable(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['numsections' => 1]);
        $cm = $generator->create_module('assign', ['course' => $course->id, 'section' => 1]);

        $label = $generator->create_module('label', ['course' => $course->id, 'section' => 1]);
        $hiddencm = $generator->create_module('assign', [
            'course' => $course->id,
            'section' => 1,
            'visible' => false,
        ]);
        $availability = '{"op":"&","c":[{"type":"date","d":">=","t":' . (time() + (2 * DAYSECS)) . '}],"showc":[false]}';
        $restrictedcm = $generator->create_module('assign', [
            'course' => $course->id,
            'section' => 0,
            'availability' => $availability,
        ]);

        // Create a student user
        $student = $generator->create_and_enrol($course, 'student');
        $this->setUser($student);

        $modinfo = get_fast_modinfo($course);
        $section = $modinfo->get_section_info(0);
        $cminfo = $modinfo->get_cm($cm->cmid);
        $navigation = new course_navigation();
        $allcms = $navigation->get_all_section_cms($modinfo, $section);

        $this->assertTrue($navigation->is_last_element($cminfo, $modinfo, $allcms));
    }

    /**
     * Test get_adjacent_section skips hidden sections (next direction) as a student.
     */
    public function test_get_adjacent_section_skips_hidden_next(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['numsections' => 5]);
        
        // Create a student user
        $student = $generator->create_and_enrol($course, 'student');
        $this->setUser($student);
        
        // Hide section 2
        $modinfo = get_fast_modinfo($course);
        $section2 = $modinfo->get_section_info(2);
        \core_courseformat\formatactions::section($course)->update($section2, ['visible' => false]);

        // Make section 3 unavailable with hidden restrictions
        $modinfo = get_fast_modinfo($course);
        $section3 = $modinfo->get_section_info(3);
        $availability = '{"op":"&","c":[{"type":"date","d":">=","t":9999999999}],"showc":[false]}';
        \core_courseformat\formatactions::section($course)->update($section3, ['availability' => $availability]);

        // Refresh modinfo after making the change
        $modinfo = get_fast_modinfo($course);
        $section1 = $modinfo->get_section_info(1);
        
        $navigation = new course_navigation();
        // From section 1, next should skip hidden section 2 and return section 3
        $adjacentsection = $navigation->get_adjacent_section($modinfo, $section1, 'next');
        
        $this->assertNotNull($adjacentsection);
        $this->assertEquals(4, $adjacentsection->section);
    }

    /**
     * Test get_adjacent_section skips hidden sections (previous direction) as a student.
     */
    public function test_get_adjacent_section_skips_hidden_previous(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['numsections' => 5]);
        
        // Create a student user
        $student = $generator->create_and_enrol($course, 'student');
        $this->setUser($student);
        
        // Hide section 2
        $modinfo = get_fast_modinfo($course);
        $section2 = $modinfo->get_section_info(2);
        \core_courseformat\formatactions::section($course)->update($section2, ['visible' => false]);
        
        // Make section 3 unavailable with hidden restrictions
        $modinfo = get_fast_modinfo($course);
        $section3 = $modinfo->get_section_info(3);
        $availability = '{"op":"&","c":[{"type":"date","d":">=","t":9999999999}],"showc":[false]}';
        \core_courseformat\formatactions::section($course)->update($section3, ['availability' => $availability]);

        // Refresh modinfo after making the change
        $modinfo = get_fast_modinfo($course);
        $section4 = $modinfo->get_section_info(4);
        
        $navigation = new course_navigation();
        // From section 3, previous should skip hidden section 2 and return section 1
        $adjacentsection = $navigation->get_adjacent_section($modinfo, $section3, 'previous');
        
        $this->assertNotNull($adjacentsection);
        $this->assertEquals(1, $adjacentsection->section);
    }
}
