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
namespace core_aigenerator;

use cache;
use cache_store;

/**
 * Provide rate limiter function for AI services.
 *
 * @package    core_aigenerator
 * @copyright  2023 Matt Porritt <matt.porritt@moodle.com>
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ratelimiter {
    /**
     * Cache key prefix.
     */
    private const CACHE_KEY_PREFIX = 'ratelimit_';

    /**
     * Request limit.
     */
    private const REQUEST_LIMIT = 10;

    /**
     * Time window.
     */
    private const TIME_WINDOW = 60;

    /**
     * Check if the user is allowed to make a request.
     *
     * @param int $userid The user ID.
     * @param string $providername Provider name
     * @return bool
     */
    public static function is_request_allowed(int $userid, string $providername): bool {
        $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'aigenerator_openai', 'user_rate');
        $userkey = self::CACHE_KEY_PREFIX . $userid . $providername;

        $ratelimitdata = $cache->get($userkey);

        if ($ratelimitdata === false) {
            // No rate limit data found for the user, allow the request and store the initial data.
            $ratelimitdata = [
                'count' => 1,
                'timestamp' => time(),
            ];
            $cache->set($userkey, $ratelimitdata);
            return true;
        }

        $currenttime = time();
        $timedifference = $currenttime - $ratelimitdata['timestamp'];

        if ($timedifference >= self::TIME_WINDOW) {
            // The time window has passed, reset the request count and update the timestamp.
            $ratelimitdata['count'] = 1;
            $ratelimitdata['timestamp'] = $currenttime;
            $cache->set($userkey, $ratelimitdata);
            return true;
        }

        if ($ratelimitdata['count'] < self::REQUEST_LIMIT) {
            // The user still has remaining requests in the time window, increment the request count and allow the request.
            $ratelimitdata['count'] += 1;
            $cache->set($userkey, $ratelimitdata);
            return true;
        }

        // The user has reached the request limit within the time window, deny the request.
        return false;
    }
}
