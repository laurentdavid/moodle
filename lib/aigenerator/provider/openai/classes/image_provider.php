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

use core\http_client;
use core_aigenerator\ai_image_provider;
use core_aigenerator\ratelimiter;
use moodle_exception;
use stdClass;

/**
 * Class ai_image_provider to generate image from a prompt
 *
 * @package    aigenerator_openai
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class image_provider implements ai_image_provider {

    /**
     * Image generation endpoint.
     */
    const IMAGE_GENERATION_ENDPOINT = 'https://api.openai.com/v1/images/generations';
    /**
     * Default size
     */
    const DEFAULT_SIZE = 256;

    /**
     * Available sizes
     */
    const AVAILABLE_SIZES = [
        256,
        512,
        1024
    ];

    /**
     * Default count.
     */
    const DEFAULT_COUNT = 1;

    /**
     * @var string $apikey API endpoint.
     */
    private string $apikey;

    /**
     * @var string $orgid Current OrgID
     */
    private string $orgid;

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
        $this->helper = new openapi_helper($this->apikey, $this->orgid, self::IMAGE_GENERATION_ENDPOINT, $client);
    }

    /**
     * Generate images matching the prompt.
     *
     * @param string $prompt
     * @param int|null $count
     * @param int|null $sizew
     * @param int|null $sizeh
     * @return array of moodle_url, one for each generated image.
     */
    public function generate_images(string $prompt, ?int $count = null, ?int $sizew = null, ?int $sizeh = null): array {
        global $USER;

        // Check rate limiting for user before continuing.
        // If rate limit is exceeded, return an error response.
        if (!ratelimiter::is_request_allowed($USER->id, 'aigenerator_openai')) {
            throw new moodle_exception('ratelimited', 'aigenerator_openai');
        }
        if (!empty($sizeh) && $sizeh != $sizew) {
            throw new moodle_exception('imagesizeerrorwidthheight', 'aigenerator_openai');
        }
        $sizew = $sizew ?? self::DEFAULT_SIZE;
        if (!in_array($sizew, self::AVAILABLE_SIZES)) {
            throw new moodle_exception('invalidsize', 'aigenerator_openai');
        }
        // Get request object.
        $requestobj = $this->generate_request_object($prompt, $count ?? self::DEFAULT_COUNT, $sizew);

        // Get response from AI service.
        $responsearr = $this->helper->query_ai_api($requestobj);
        if (isset($responsearr['errorcode'])) {
            throw new moodle_exception('openaierror', 'aigenerator_openai', '', $responsearr);
        }
        // Fetch URL and convert them to moodle URLs.
        $urls = [];
        if (empty($responsearr['data'])) {
            return [];
        }
        foreach ($responsearr['data'] as $result) {
            $urls[] = new \moodle_url($result['url']);
        }
        return $urls;
    }

    /**
     * Generate request object ready to send to the AI service.
     *
     * @param string $prompttext The prompt text.
     * @param int $count
     * @param int $size
     * @return stdClass
     */
    private function generate_request_object(string $prompttext, int $count, int $size): stdClass {
        // Create the AI request object.
        $requestobj = new stdClass();
        $requestobj->size = "{$size}x{$size}";
        $requestobj->n = $count;
        $requestobj->response_format = 'url';
        $requestobj->prompt = $prompttext;

        return $requestobj;
    }
}
