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
    /**
     * Constructor.
     *
     * @param array<string,string> $anchorrules map of path regex => anchor key
     */
    public function __construct(
        private array $anchorrules = []
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $method = strtoupper($request->getMethod());
        $path   = $request->getUri()->getPath() ?? '/';

        // --- BEFORE: drop anchor on eligible GET requests.
        if ($method === 'GET' && !$this->is_ajax($request)) {
            navigation_anchor_manager::push_referer_from_request($request);
        }

        // --- HANDLER
        $response = $handler->handle($request);

        // --- AFTER: if handler flagged completion, try to redirect to anchor.
        // The handler can set:
        //   X-Nav-Anchor: <key>       -> use that anchor key
        // or X-Nav-Anchor: 1          -> use first matching rule for this path
        // Optional:
        //   X-Nav-Fallback: <local URL> (string path or full local URL)
        $anchorHeader = $response->getHeaderLine('X-Nav-Anchor');
        if ($anchorHeader !== '') {
            $key = $this->resolve_key($anchorHeader, $path);
            if ($key !== null) {
                $fallback = $this->fallback_from_header($response, $request) ?? new \core\url('/');
                if (navigation_anchor_manager::has($key)) {
                    $target = navigation_anchor_manager::pop($key);
                } else {
                    $target = $fallback;
                }
                // Only rewrite if this is a redirect (3xx + Location) OR if handler returned 200 and wants us to issue a redirect.
                $status = $response->getStatusCode();
                $location = $response->getHeaderLine('Location');
                if($status < 300 || $status >= 400) {
                    $response = $response->withStatus(302);
                }
                return $this->with_redirect_to($response, $target);
            }
        }

        return $response;
    }

    /**
     * Resolve the anchor key from header value.
     * If header is '1', use first matching rule for this path.
     * Otherwise, use the header value as explicit key.
     *
     * @param string $header
     * @param string $path
     * @return string|null
     */
    private function resolve_key(string $header, string $path): ?string {
        $h = trim($header);
        if ($h !== '1') {
            return $h; // explicit key
        }
        foreach ($this->anchorrules as $pattern => $key) {
            if (preg_match($pattern, $path)) return $key;
        }
        return null;
    }

    /**
     * Check if the request is an AJAX request.
     *
     * @param ServerRequestInterface $req
     * @return bool
     */
    private function is_ajax(ServerRequestInterface $req): bool {
        $val = $req->getHeaderLine('X-Requested-With');
        return strtolower($val) === 'xmlhttprequest';
    }

    /**
     * Get the current local URL from the request.
     * This is the path + query part only, without scheme/host/port.
     *
     * @param ServerRequestInterface $req
     * @return \core\url
     */
    private function current_local_url(ServerRequestInterface $req): \core\url {
        $uri = $req->getUri();
        $path = $uri->getPath() ?: '/';
        $query = $req->getUri()->getQuery();
        $raw = $path . ($query ? ('?' . $query) : '');
        return new \core\url($raw);
    }

    /**
     * Parse and validate the fallback URL from header.
     * Only local URLs are allowed.
     *
     * @param ResponseInterface $resp
     * @param ServerRequestInterface $req
     * @return \core\url|null
     */
    private function fallback_from_header(ResponseInterface $resp, ServerRequestInterface $req): ?\core\url {
        $h = trim($resp->getHeaderLine('X-Nav-Fallback'));
        if ($h === '') return null;
        try {
            $u = new \core\url($h);
            // Only allow local
            $out = $u->out(false);
            if (str_starts_with($out, 'http://') || str_starts_with($out, 'https://')) return null;
            return $u;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Helper to rewrite a redirect response.
     *
     * @param ResponseInterface $resp
     * @param \core\url $to
     * @return ResponseInterface
     */
    private function with_redirect_to(ResponseInterface $resp, \core\url $to): ResponseInterface {
        return $resp
            ->withHeader('Location', $to->out(false))
            ->withStatus(302); // force 302 for safety
    }
}