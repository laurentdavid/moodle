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

use context_course;
use core\navigation\navigation_anchor_manager;
use core\router\parameters\query_returnurl;
use core\router\require_login;
use core\router\route;
use core_course\route\parameters\path_section;
use moodle_page;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use core_courseformat\base as course_format;
/**
 * Section Management.
 *
 * @package    core_course
 * @copyright  Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_management {
    use \core\router\route_controller;

    /**
     * Key used to store the anchor in the navigation manager.
     */
    public const SECTION_EDIT_RETURN_ANCHOR_KEY = 'section-edit-return';
    /**
     * Edit a section
     *
     * @param ResponseInterface $response
     * @param \stdClass $section
     * @param int $courseid
     * @param context_course $sectioncontext
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    #[route(
        path: '/{course}/section/{section}/edit',
        method: ['GET', 'POST'],
        pathtypes: [
            new path_section(),
        ],
        queryparams: [
            new query_returnurl(),
        ],
        requirelogin: new require_login(
            requirelogin: true,
            courseattributename: 'course',
        ),
    )]
    public function edit(
        ResponseInterface $response,
        \stdClass $section,
        int $courseid,
        context_course $sectioncontext,
        ServerRequestInterface $request,
    ): ResponseInterface {
        global $PAGE, $OUTPUT, $CFG;
        require_once($CFG->libdir . '/formslib.php');
        if (!has_capability('moodle/course:update', \context_course::instance($courseid))) {
            return $response->withStatus(403)->withHeader('Content-Type', 'text/plain')
                ->withBody(\GuzzleHttp\Psr7\Utils::streamFor('Forbidden'));
        }
        $courseformat = course_format::instance($courseid);

        $sectioninfo = get_fast_modinfo($courseid)->get_section_info_by_id($section->id);
        if ($sectioninfo->name) {
            $defaultsectionname = $sectioninfo->name;
        } else {
            $defaultsectionname = $courseformat->get_default_section_name($section);
        }
        $editoroptions = [
            'context'   => $sectioncontext,
            'maxfiles'  => EDITOR_UNLIMITED_FILES,
            'maxbytes'  => $CFG->maxbytes,
            'trusttext' => false,
            'noclean'   => true,
            'subdirs'   => true,
        ];

        $customdata = [
            'cs' => $sectioninfo,
            'editoroptions' => $editoroptions,
            'defaultsectionname' => $defaultsectionname,
            'showonly' => $this->get_param($request, 'showonly', 0),
            'returnurl' => $this->get_param($request, 'returnurl', null) ,
        ];
        $PAGE->set_context($sectioncontext);
        $course = get_course($courseid);
        $PAGE->set_course($course);
        $PAGE->add_body_class('course-section-edit');
        $mform = $courseformat->editsection_form($PAGE->url, $customdata);
        // Set current value, make an editable copy of section_info object
        // this will retrieve all format-specific options as well.
        $initialdata = convert_to_array($sectioninfo);
        if (!empty($CFG->enableavailability)) {
            $initialdata['availabilityconditionsjson'] = $sectioninfo->availability;
        }
        $mform->set_data($initialdata);
        if (!empty($showonly)) {
            $mform->filter_shown_headers(explode(',', $showonly));
        }
        $returnurlstring = $this->get_param($request, 'returnurl') ?? course_get_url($course);
        $returnurl = new \core\url($returnurlstring);
        if (navigation_anchor_manager::has(self::SECTION_EDIT_RETURN_ANCHOR_KEY)) {
            $returnurl = navigation_anchor_manager::get(self::SECTION_EDIT_RETURN_ANCHOR_KEY);
        }
        if ($mform->is_cancelled()) {
            // Form cancelled, return to course.
            return self::redirect($response, $returnurl);
        } else if ($data = $mform->get_data()) {
            // Data submitted and validated, update and return to course.

            // For consistency, we set the availability field to 'null' if it is empty.
            if (!empty($CFG->enableavailability)) {
                // Renamed field.
                $data->availability = $data->availabilityconditionsjson;
                unset($data->availabilityconditionsjson);
                if ($data->availability === '') {
                    $data->availability = null;
                }
            }
            course_update_section($courseid, $section, $data);

            $PAGE->navigation->clear_cache();
            if ($data->returnurl) {
                $returnurl = new \core\url($data->returnurl);
            }
            return self::redirect($response, $returnurl);
        }

        $sectionname = get_section_name($courseid, $sectioninfo);
        $stredit = get_string('editsectiontitle', '', $sectionname);
        $strsummaryof = get_string('editsectionsettings');
        $PAGE->set_title($stredit . moodle_page::TITLE_SEPARATOR . $course->shortname);
        $PAGE->set_heading($course->fullname);
        $PAGE->navbar->add($stredit);
        $response->getBody()->write($OUTPUT->header());
        $response->getBody()->write($OUTPUT->heading($strsummaryof));
        $response->getBody()->write($mform->render());
        $response->getBody()->write($OUTPUT->footer());
        return $response;
    }
}
