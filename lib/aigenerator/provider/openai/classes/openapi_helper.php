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
use GuzzleHttp\Psr7\Response;
use stdClass;

/**
 * Provide access to the open API backend
 *
 * @package    aigenerator_openai
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @copyright  2023 Matt Porritt <matt.porritt@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class openapi_helper {
    /**
     * Default to completion endpoint.
     */
    const AI_DEFAULT_ENDPOINT = 'https://api.openai.com/v1/completions';
    /**
     * @var http_client $client HTTP client.
     */
    private http_client $client;
    /**
     * @var string $apiendpoint API Endpoint.
     */
    private string $apiendpoint;

    /**
     * Construct
     *
     * @param string $apikey
     * @param string|null $orgid
     * @param string|null $apiendpoint
     * @param http_client|null $client The http client for testing only.
     */
    public function __construct(string $apikey, ?string $orgid = null, ?string $apiendpoint = null, ?http_client $client = null) {
        $this->apiendpoint = $apiendpoint ?? self::AI_DEFAULT_ENDPOINT;
        // Allow for dependency injection of http client.
        if ($client) {
            $this->client = $client;
        } else {
            // Create http client.
            $this->client = new http_client([
                'base_uri' => $this->apiendpoint,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $apikey,
                    'OpenAI-Organization' => $orgid,
                ]
            ]);
        }
    }

    /**
     * Query the AI service.
     *
     * @param stdClass $requestobj The request object.
     * @return array The response from the AI service.
     */
    public function query_ai_api(stdClass $requestobj): array {
        // Create the AI request object.
        $requestjson = json_encode($requestobj);

        // Call the external AI service.
        $response = $this->client->post('', [
            'body' => $requestjson,
        ]);

        // Handle the various response codes.
        $status = $response->getStatusCode();
        if ($status == 200) {
            return $this->handle_api_success($response);
        } else {
            return $this->handle_api_error($status, $response);
        }
    }

    /**
     * Handle a successful response from the external AI api.
     *
     * @param Response $response The response object.
     * @return array The response.
     */
    private function handle_api_success(Response $response): array {
        $responsebody = $response->getBody();
        try {
            return json_decode($responsebody->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [
                'errorcode' => $e->getCode(),
                'error' => 'JSON:' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle an error from the external AI api.
     *
     * @param int $status The status code.
     * @param Response $response The response object.
     * @return array The error response.
     */
    private function handle_api_error(int $status, Response $response): array {
        if ($status == 500) {
            $responsearr = [
                'errorcode' => $status,
                'error' => 'Internal server error.',
            ];
        } else if ($status == 503) {
            $responsearr = [
                'errorcode' => $status,
                'error' => 'Service unavailable.',
            ];
        } else {
            $responsebody = $response->getBody();
            $bodyobj = json_decode($responsebody->getContents());
            $responsearr = [
                'errorcode' => $status,
                'error' => $bodyobj->error->message,
            ];
        }

        return $responsearr;
    }
}
