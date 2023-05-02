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

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

/**
 * Unit tests for the text generator in the OpenAI plugin.
 *
 * @package   aigenerator_openai
 * @category   test
 * @copyright   2023 Matt Porritt <matt.porritt@moodle.com>
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @coversDefaultClass \aigenerator_openai\text_provider
 */
class text_provider_test extends \advanced_testcase {
    /**
     * Setup the module
     *
     * @return void
     */
    public function setUp(): void {
        $this->resetAfterTest(true);
        set_config('apikey', 'abc123', 'aigenerator_openai');
        set_config('orgid', 'abc123', 'aigenerator_openai');
    }

    /**
     * Test the generate content function.
     *
     * @return void
     * @covers \aigenerator_openai\text_provider::generate_text
     */
    public function test_generate_content() {
        $prompttext = 'Say this is a test';
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/fixtures/valid_text_json_answer.txt')),
        ]);
        $client = new \core\http_client(['mock' => $mock]);
        $textprovider = new text_provider($client);

        $result = $textprovider->generate_text($prompttext);
        $this->assertStringContainsString("A generated text", $result);
    }

    /**
     * Test the generate content function.
     *
     * @return void
     * @covers \aigenerator_openai\text_provider::generate_text
     */
    public function test_generate_content_api_fails() {
        $prompttext = 'Say this is a test';
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/fixtures/invalid_text_json_answer.txt')),
        ]);
        $client = new \core\http_client(['mock' => $mock]);
        $textprovider = new text_provider($client);

        $this->expectExceptionMessage('Error when calling OpenAI API: (Error 4) - JSON:Syntax error.');
        $textprovider->generate_text($prompttext);
    }
}
