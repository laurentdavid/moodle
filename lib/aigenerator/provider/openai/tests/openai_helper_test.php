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

use GuzzleHttp\Psr7\Response;
use ReflectionMethod;

/**
 * Unit tests for the openai_helper class in the OpenAI plugin.
 *
 * @package   aigenerator_openai
 * @category   test
 * @copyright   2023 Matt Porritt <matt.porritt@moodle.com>
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @coversDefaultClass \aigenerator_openai\openapi_helper
 */
class openai_helper_test extends \advanced_testcase {
    /**
     * Test the API success response handler method.
     *
     * @return void
     * @covers \aigenerator_openai\openapi_helper::handle_api_success
     */
    public function test_handle_api_success() {
        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/fixtures/valid_text_json_answer.txt')
        );
        // We're testing a private method, so we need to setup reflector magic.
        $ai = new \aigenerator_openai\openapi_helper("");
        $method = new ReflectionMethod($ai, 'handle_api_success');
        $method->setAccessible(true); // Allow accessing of private method.

        $result = $method->invoke($ai, $response);
        $this->assertStringContainsString('A generated text', $result['choices'][0]['text']);
    }

    /**
     * Test the API error response handler method.
     *
     * @return void
     * @covers \aigenerator_openai\openapi_helper::handle_api_error
     */
    public function test_handle_api_error() {
        $responses = [
            500 => new Response(500, ['Content-Type' => 'application/json']),
            503 => new Response(503, ['Content-Type' => 'application/json']),
            401 => new Response(401, ['Content-Type' => 'application/json'],
                '{"error": {"message": "Invalid Authentication"}}'),
            404 => new Response(404, ['Content-Type' => 'application/json'],
                '{"error": {"message": "You must be a member of an organization to use the API"}}'),
            429 => new Response(429, ['Content-Type' => 'application/json'],
                '{"error": {"message": "Rate limit reached for requests"}}'),
        ];

        $ai = new \aigenerator_openai\openapi_helper("");
        $method = new ReflectionMethod($ai, 'handle_api_error');
        $method->setAccessible(true); // Allow accessing of private method.

        foreach ($responses as $status => $response) {
            $result = $method->invoke($ai, $status, $response);
            $this->assertEquals($status, $result['errorcode']);
            if ($status == 500) {
                $this->assertEquals('Internal server error.', $result['error']);
            } else if ($status == 503) {
                $this->assertEquals('Service unavailable.', $result['error']);
            } else {
                $this->assertStringContainsString($response->getBody()->getContents(), $result['error']);
            }
        }
    }
}
