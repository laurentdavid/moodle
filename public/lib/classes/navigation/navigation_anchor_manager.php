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

namespace core\navigation;

use core\url;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This class manages navigation anchors stored in session.
 *
 * @package   core
 * @category  navigation
 * @copyright 2025 Laurent David <laurent.david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navigation_anchor_manager {
    /**
     * Session key to store anchors.
     */
    private const KEY = 'navigation_anchors';

    /**
     * Set an anchor for a named flow key, with optional TTL (seconds).
     * Ignores external URLs for safety.
     * Minimum TTL is 60s, default is 900s (15min).
     * If the URL is external, it is ignored for safety.
     *
     * @param string $key the anchor key
     * @param url $url the anchor URL
     */
    public static function push(string $key, url $url): void {
        global $SESSION;
        if (!$url->is_local_url()) {
            return; // ignore unsafe
        }
        $SESSION->{self::KEY} ??= [];
        $SESSION->{self::KEY}[$key] = $url->out(false);
    }

    /**
     * Set an anchor for a named flow key, with optional TTL (seconds).
     * Ignores external URLs for safety.
     * Minimum TTL is 60s, default is 900s (15min).
     * If the URL is external, it is ignored for safety.
     *
     * @param ServerRequestInterface $request the current request
     */
    public static function push_referer_from_request(ServerRequestInterface $request): void {
        $anchorkey = $request->getAttribute('setnavanchoreferer');
        if ($anchorkey) {
            $serverparams = $request->getServerParams();
            $referer = $serverparams['HTTP_REFERER'] ?? null;
            if ($referer) {
                $url = new \core\url($referer);
                if ($url->is_local_url()) {
                    self::push($anchorkey, $url);
                }
            }
        }
    }
    /**
     * Return the anchor URL if present and fresh; otherwise null.
     *
     * @param string $key the anchor key
     * @return url|null the anchor URL or null if missing/expired
     */
    public static function pop(string $key): ?url {
        $url = self::get($key); // to ensure the key is removed
        if ($url) {
            self::clear($key);
        }
        return $url;
    }

    /**
     * Return the anchor URL if present, otherwise null.
     *
     * @param string $key the anchor key
     * @return url|null the anchor URL or null if missing/expired
     */
    public static function get(string $key): ?url {
        global $SESSION;
        $entry = $SESSION->{self::KEY}[$key] ?? null;
        if (!$entry) {
            return null;
        }

        $url = new  url($entry);
        return $url->is_local_url() ? $url : null;
    }

    /**
     * Clear the anchor for a named flow key.
     *
     * @param string $key the anchor key
     */
    public static function clear(string $key): void {
        global $SESSION;
        unset($SESSION->{self::KEY}[$key]);
    }

    /**
     * Returns whether an anchor exists for a named flow key.
     *
     * @param string $key the anchor key
     * @return bool true if an anchor exists, false otherwise
     */
    public static function has(string $key):bool {
        global $SESSION;
        return  !empty($SESSION->{self::KEY}[$key]);
    }
}