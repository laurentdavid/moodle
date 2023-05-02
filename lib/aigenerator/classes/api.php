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

use core_plugin_manager;
use moodle_exception;

/**
 * API for AI Generator.
 *
 * This consists in generic method to generate text and images from a prompt
 *
 * @package    core_aigenerator
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /**
     * Text generator type.
     */
    const PROVIDER_TEXT = 'text';
    /**
     * Image generator type.
     */
    const PROVIDER_IMAGE = 'image';
    /**
     * User prefered generator.
     */
    const USER_GENERATOR_PREFERENCE_NAME = 'ai_generator_preferred_generator';

    /**
     * @var array $providers associative array with the providers for this provider name.
     */
    private array $providers;

    /**
     * Create an instance of an API for a given provider.
     *
     * @param string $providercomponent
     * @param array|null $params
     * @throws moodle_exception
     */
    private function __construct(string $providercomponent, ?array $params) {
        foreach (self::get_provider_types() as $type) {
            $classname = $this->get_class_name_for_provider($providercomponent, $type);
            if (class_exists($classname)) {
                $this->providers[$type] = new $classname();
            }
        }
        if (empty($this->providers)) {
            throw new moodle_exception('providernotimplemented', 'core_aigenerator', '', $providercomponent);
        }
    }

    /**
     * Get provider types
     *
     * @return string[]
     */
    public static function get_provider_types(): array {
        return [self::PROVIDER_IMAGE, self::PROVIDER_TEXT];
    }

    /**
     * Get class name for providers
     *
     * @param string $providercomponent
     * @param string $type
     * @return string
     */
    protected function get_class_name_for_provider(string $providercomponent, string $type): string {
        return "$providercomponent\\{$type}_provider";
    }

    /**
     * Load the AI Generator assigned for this user or the default one if none specified
     *
     * @param array|null $params
     * @return api|null the first api in the sort order or the one selected by the user.
     */
    public static function load(?array $params = []): ?self {
        $availableaigenerators = core_plugin_manager::instance()->get_plugins_of_type('aigenerator');
        if (empty($availableaigenerators)) {
            return null;
        }
        // Now sort the plugins that are available.
        // Take the first.
        usort($availableaigenerators, function($p1, $p2) {
            return $p1->sortorder <=> $p2->sortorder;
        });
        $selectedprovider = current($availableaigenerators);
        // Check if there is an override for a given user and take this one.
        if ($useraigenerator = get_user_preferences(self::USER_GENERATOR_PREFERENCE_NAME)) {
            $availablegeneratornames = array_map(function($provider) {
                return $provider->name;
            }, $availableaigenerators);
            if ($key = array_search($useraigenerator, $availablegeneratornames, true)) {
                $selectedprovider = $availableaigenerators[$key];
            }
        }
        return new self($selectedprovider->component, $params);
    }

    /**
     * Get image provider
     *
     * @return ai_image_provider|null
     */
    public function get_image_provider(): ?ai_image_provider {
        return $this->providers[self::PROVIDER_IMAGE] ?? null;
    }

    /**
     * Get text generator provider
     *
     * @return ai_text_provider|null
     */
    public function get_text_provider(): ?ai_text_provider {
        return $this->providers[self::PROVIDER_TEXT] ?? null;
    }
}
