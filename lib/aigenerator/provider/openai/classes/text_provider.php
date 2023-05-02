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

namespace aigenerator_openai;

use cache;
use cache_store;
use core\http_client;
use core_aigenerator\ai_text_provider;
use core_aigenerator\ratelimiter;
use moodle_exception;
use stdClass;

/**
 * Class ai_text_provider to generate text from a prompt
 *
 * @package    aigenerator_openai
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @copyright  2023 Matt Porritt <matt.porritt@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_provider implements ai_text_provider {
    /**
     * Completion endpoint.
     */
    const COMPLETION_ENDPOINT = 'https://api.openai.com/v1/completions';
    /**
     * Default max token.
     */
    const DEFAULT_MAX_TOKEN = 2048;

    /**
     * Default model.
     */
    const DEFAULT_MODEL = 'text-davinci-003';

    /**
     * Default temperature.
     */
    const DEFAULT_TEMPERATURE = 0.3;

    /**
     * @var string $apikey API endpoint.
     */
    private string $apikey;

    /**
     * @var string $orgid Current OrgID
     */
    private string $orgid;

    /**
     * @var string $model Model for the queries
     */
    private string $model;

    /**
     * @var int $maxtoken Max tokens
     */
    private int $maxtoken;

    /**
     * @var float $temperature temperature for the first query
     */
    private float $temperature; // Set your desired temperature for first query.

    /**
     * @var openapi_helper helper
     */
    private openapi_helper $helper;

    /**
     * Class constructor.
     *
     * @param http_client|null $client The http client for testing only.
     */
    public function __construct(?http_client $client = null) {
        // Get api key from config.
        $this->apikey = get_config('aigenerator_openai', 'apikey');
        // Get api org id from config.
        $this->orgid = get_config('aigenerator_openai', 'orgid');
        $this->maxtoken = intval(get_config('aigenerator_openai', 'textmaxtoken'));
        if (!$this->maxtoken) {
            $this->maxtoken = self::DEFAULT_MAX_TOKEN;
        }
        $this->model = get_config('aigenerator_openai', 'textmodel');
        if (!$this->model) {
            $this->model = self::DEFAULT_MODEL;
        }
        $this->temperature = get_config('aigenerator_openai', 'texttemperature');
        if (!$this->temperature) {
            $this->temperature = self::DEFAULT_TEMPERATURE;
        }
        $this->helper = new openapi_helper($this->apikey, $this->orgid, self::COMPLETION_ENDPOINT, $client);
    }

    /**
     * Generate text completing the prompt
     *
     * @param string $prompt
     * @return string
     */
    public function generate_text(string $prompt): string {
        global $USER;

        // Check rate limiting for user before continuing.
        // If rate limit is exceeded, return an error response.
        if (!ratelimiter::is_request_allowed($USER->id, 'aigenerator_openai')) {
            throw new moodle_exception('ratelimited', 'aigenerator_openai');
        }

        // Update temperature.
        $this->update_temperature($prompt, $USER->id);

        // Get request object.
        $requestobj = $this->generate_request_object($prompt);

        // Get response from AI service.
        $responsearr = $this->helper->query_ai_api($requestobj);
        if (isset($responsearr['errorcode'])) {
            throw new moodle_exception('openaierror', 'aigenerator_openai', '', $responsearr);
        }
        return $responsearr['choices'][0]['text'] ?? '';
    }

    /**
     * Get the current temperature for the AI service.
     *
     * @param string $prompttext The prompt text.
     * @param int $userid The user id.
     * @return void
     */
    private function update_temperature(string $prompttext, int $userid): void {
        // Set up the cache API for the Tiny AI Plugin.
        $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'aigenerator_openai', 'request_temperature');
        $cachekeystr = $prompttext . (string) $userid;
        $cachekey = hash_pbkdf2('sha3-256', $cachekeystr, 'aigenerator_openai', 1);

        // Check cache for existing response.
        // If response is a hit then a response has already been generated for this prompt,
        // and we increase the temperature to generate a new response.
        if ($cache->get($cachekey)) {
            $this->temperature = $cache->get($cachekey) + 0.1;
        }

        // Max allowed temperature is 2.
        if ($this->temperature > 2) {
            $this->temperature = 2;
        }

        // Update the cache.
        $cache->set($cachekey, $this->temperature);
    }

    /**
     * Generate request object ready to send to the AI service.
     *
     * @param string $prompttext The prompt text.
     */
    private function generate_request_object(string $prompttext): stdClass {
        // Create the AI request object.
        $requestobj = new stdClass();
        $requestobj->model = $this->model;
        $requestobj->temperature = $this->temperature;
        $requestobj->prompt = $prompttext;
        $requestobj->max_tokens = $this->maxtoken;

        return $requestobj;
    }
}
