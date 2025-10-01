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

namespace core_course\route\shim;

use core\di;
use core\param;
use core\router\parameters\query_returnurl;
use core\router\route;
use core\router\route_controller;
use core\router\schema\parameters\query_parameter;
use moodle_database;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A shim for the course routes.
 *
 * @package    core_course
 * @copyright  Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_routes {
    use route_controller;

    /**
     * Shim /course/admin.php to the course management controller.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    #[route(
        path: '/admin.php',
        queryparams: [
            new query_parameter(
                name: 'courseid',
                type: param::INT,
                description: 'The course ID',
                required: true,
            ),
        ],
    )]
    public function administer_course(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        $params = $request->getQueryParams();
        return self::redirect_to_callable(
            $request,
            $response,
            [\core_course\route\controller\course_management::class, 'administer_course'],
            pathparams: $params + ['course' => $params['courseid']],
            excludeparams: ['courseid'],
        );
    }

    /**
     * Shim /course/tags.php to the course tag management controller.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    #[route(
        path: '/tags.php',
        queryparams: [
            new query_parameter(
                name: 'id',
                type: param::INT,
                description: 'The course ID',
                required: true,
            ),
            new query_parameter(
                name: 'returnurl',
                type: param::LOCALURL,
                description: 'The return URL',
                required: false,
            ),
        ],
    )]
    public function administer_tags(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        $params = $request->getQueryParams();
        return self::redirect_to_callable(
            $request,
            $response,
            [\core_course\route\controller\tags_controller::class, 'administer_tags'],
            pathparams: $params + ['course' => $params['id']],
            excludeparams: ['id'],
        );
    }

    /**
     * Shim /course/editsection.php to the section management controller.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    #[route(
        path: '/editsection.php',
        queryparams: [
            new query_parameter(
                name: 'id',
                type: param::INT,
                description: 'The section ID',
                required: true,
            ),
            new query_parameter(
                name: 'sr',
                type: param::INT,
                description: 'The section return',
                required: false,
                default: null,
            ),
            new query_parameter(
                name: 'delete',
                type: param::BOOL,
                description: 'Delete the section',
                required: false,
                default: false,
            ),
            new query_parameter(
                name: 'showonly',
                type: param::TAGLIST,
                description: 'Show only tags',
                required: false,
                default: 0,
            ),
            new query_returnurl(),
        ],
    )]
    public function edit_section(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {
        $db  = di::get_container()->get(moodle_database::class); // We could have done that by injection too but there
        // is a bug as $DB is not defined in the constructor.
        $params = $request->getQueryParams();
        $params['section'] = $params['id'];
        $data = $db->get_record('course_sections', [
            'id' => $params['id'],
        ]);
        return self::redirect_to_callable(
            $request,
            $response,
            [\core_course\route\controller\section_management::class, 'edit'],
            pathparams: $params + ['course' => $data->course],
            excludeparams: ['id'],
        );
    }
}
