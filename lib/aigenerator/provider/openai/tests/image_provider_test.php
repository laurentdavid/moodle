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
 * Unit tests for image generator in the OpenAI plugin.
 *
 * @package   aigenerator_openai
 * @category   test
 * @copyright   2023 Matt Porritt <matt.porritt@moodle.com>
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @coversDefaultClass \aigenerator_openai\image_provider
 */
class image_provider_test extends \advanced_testcase {
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
     * @covers \aigenerator_openai\image_provider::generate_images
     */
    public function test_generate_content() {
        $prompttext = 'Create an image';
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/fixtures/valid_image_json_answer.txt')),
        ]);
        $client = new \core\http_client(['mock' => $mock]);
        $imageprovider = new \aigenerator_openai\image_provider($client);

        $result = $imageprovider->generate_images($prompttext);
        $result = array_map(function($url) {
            return $url->out(false);
        }, $result);
        $this->assertEquals(
            [
                'https://moodle.com/image1',
                'https://moodle.com/image2',
            ],
            $result
        );
    }

    /**
     * Test the generate content function with invalid size.
     *
     * @return void
     * @covers \aigenerator_openai\image_provider::generate_images
     */
    public function test_generate_content_with_wrong_size_parameters() {
        $prompttext = 'Create an image';
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/fixtures/valid_image_json_answer.txt')),
        ]);
        $client = new \core\http_client(['mock' => $mock]);
        $imageprovider = new \aigenerator_openai\image_provider($client);

        $this->expectExceptionMessage('Specified width and height should be 256, 512 or 1024.');
        $imageprovider->generate_images($prompttext, 1, 513);
    }

    /**
     * Test the generate content function with invalid size.
     *
     * @return void
     * @covers \aigenerator_openai\image_provider::generate_images
     */
    public function test_generate_content_width_height_not_equal() {
        $prompttext = 'Create an image';
        $mock = new MockHandler([
            new Response(200, [], file_get_contents(__DIR__ . '/fixtures/valid_image_json_answer.txt')),
        ]);
        $client = new \core\http_client(['mock' => $mock]);
        $imageprovider = new \aigenerator_openai\image_provider($client);

        $this->expectExceptionMessage('Specified width and height should be equal for Image generation');
        $imageprovider->generate_images($prompttext, 1, 512, 12);
    }
}
