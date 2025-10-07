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

namespace core\router\middleware;

use core\navigation\navigation_anchor_manager;
use core\router\util;
use core\router\route_loader_interface;
use core\url;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to set flags and define setup.
 *
 * @package    core
 * @copyright  2025 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navigation_anchor_middleware implements MiddlewareInterface {
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $method = strtoupper($request->getMethod());

        // Make sure if there is an anchor referer request, we push it as an anchor.
        if ($method === 'GET' && !$this->is_ajax($request)) {
            navigation_anchor_manager::set_referer_from_request($request);
        }
        $response = $handler->handle($request);
        return $response;
    }

    /**
     * Check if the request is an AJAX request.
     *
     * @param ServerRequestInterface $req
     * @return bool
     */
    private function is_ajax(ServerRequestInterface $req): bool {
        return !defined('AJAX_SCRIPT') || AJAX_SCRIPT != '0';
    }
}
