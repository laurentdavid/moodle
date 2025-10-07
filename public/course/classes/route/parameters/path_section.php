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

namespace core_course\route\parameters;

use core\exception\not_found_exception;
use core\param;
use core\router\schema\example;
use core\router\schema\parameters\mapped_property_parameter;
use core\router\schema\parameters\path_parameter;
use core\router\schema\referenced_object;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A parameter representing a course section.
 *
 * @package    core_course
 * @copyright  Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class path_section extends path_parameter implements mapped_property_parameter, referenced_object {
    /**
     * Create a new instance of the path_section.
     *
     * @param string $name The name of the parameter to use for the identifier
     * @param mixed ...$extra Additional arguments
     **/
    public function __construct(
        string $name = 'section',
        ...$extra,
    ) {
        $extra['name'] = $name;
        $extra['type'] = param::INT;
        $extra['description'] = <<<EOF
        The section identifier.

        This can be the id of the section, or the section number within a course.
        When using section number, the course parameter must also be provided in the route.
        EOF;
        $extra['examples'] = [
            new example(
                name: 'A section id',
                value: 123,
            ),
            new example(
                name: 'A section number (requires course parameter)',
                value: 2,
            ),
        ];

        parent::__construct(...$extra);
    }

    /**
     * Get the section record for the given value.
     *
     * @param string $value The value to look up
     * @param int|null $courseid The course id if looking up by section number
     * @return mixed The section record
     * @throws not_found_exception If the section is not found
     */
    protected function get_section_for_value(string $value, ?int $courseid = null): mixed {
        global $DB;
        if (!$courseid) {
            $data = $DB->get_record('course_sections', [
                'id' => $value,
            ]);
        } else {
            $data = $DB->get_record('course_sections', [
                'course' => $courseid,
                'section' => $value,
            ]);
        }

        if ($data) {
            return $data;
        }

        throw new not_found_exception('section', $value);
    }

    #[\Override]
    public function add_attributes_for_parameter_value(
        ServerRequestInterface $request,
        string $value,
    ): ServerRequestInterface {
        $course = $request->getAttribute('course');
        $courseid = $course ? $course->id : null;

        $section = $this->get_section_for_value($value, $courseid);
        return $request
            ->withAttribute($this->name, $section)
            ->withAttribute("{$this->name}context", \core\context\course::instance($section->course))
            ->withAttribute("courseid", $section->course);
    }

    #[\Override]
    public function get_schema_from_type(param $type): \stdClass {
        $schema = parent::get_schema_from_type($type);
        $schema->pattern = "^\d+$";
        return $schema;
    }
}
