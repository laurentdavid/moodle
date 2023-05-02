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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/aigenerator_test_helper_trait.php');

/**
 * Class api_test to test the aigenerator public api and its associated methods.
 *
 * @package    core_aigenerator
 * @category   test
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \core_aigenerator\api
 */
class api_test extends \advanced_testcase {
    use aigenerator_test_helper_trait;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $this->setup_configs();
    }

    /**
     * Test get_image_provider method.
     *
     * @param string $providername
     * @param array $expectedproviders
     * @covers ::get_image_provider
     * @dataProvider get_providers
     */
    public function test_get_provider(string $providername, array $expectedproviders): void {
        $this->setup_fake_providers();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        set_user_preference(api::USER_GENERATOR_PREFERENCE_NAME, $providername);
        $aigenerator = api::load();

        foreach (api::get_provider_types() as $type) {
            $getprovidercallback = "get_{$type}_provider";
            if (isset($expectedproviders[$type])) {
                $provider = $aigenerator->$getprovidercallback();
                $this->assertEquals($expectedproviders[$type], $provider ? get_class($provider) : null);
            }
        }

    }

    /**
     * Get providers info for tests.
     *
     * @return array[]
     */
    public function get_providers(): array {
        return [
            'singleprovider test' => [
                'providername' => 'singleprovider',
                'providers' => [
                    'image' => 'aigenerator_singleprovider\image_provider',
                    'text' => null,
                ]
            ],
            'sample provider test' => [
                'providername' => 'sampleprovider',
                'providers' => [
                    'image' => 'aigenerator_sampleprovider\image_provider',
                    'text' => 'aigenerator_sampleprovider\text_provider',
                ]
            ]
        ];
    }
}

