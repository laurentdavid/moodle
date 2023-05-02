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

use core_component;
use ReflectionClass;

/**
 * Trait aigenerator_test_helper_trait to generate initial setup for aigenerator providers.
 *
 * @package    core_aigenerator
 * @category   test
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait aigenerator_test_helper_trait {
    /**
     * Setup necessary configs for aigenerator subsystem.
     *
     * @return void
     */
    protected function setup_configs(): void {
        set_config('enableaigeneratorsubsystem', 1);
    }

    /**
     * Helper
     *
     * @return void
     */
    protected function setup_fake_providers(): void {
        global $CFG;
        require_once("$CFG->libdir/upgradelib.php");
        $providerspath = "lib/aigenerator/tests/fixtures/provider";
        // This is similar to accesslib_test::setup_fake_plugin.
        $mockedcomponent = new ReflectionClass(core_component::class);

        $mockedcomponentsource = $mockedcomponent->getProperty('componentsource');
        $mockedcomponentsource->setAccessible(true);
        $componentsource = $mockedcomponentsource->getValue();
        $componentsource['plugintypes']->aigenerator = $providerspath;
        $mockedcomponentsource->setValue($componentsource);

        $fillallcaches = $mockedcomponent->getMethod('fill_all_caches');
        $fillallcaches->setAccessible(true);
        $fillallcaches->invoke(null);

        // Make sure the plugin is installed.
        ob_start();
        upgrade_noncore(false);
        upgrade_finished();
        ob_end_clean();
        \core_plugin_manager::reset_caches();
        $this->resetDebugging(); // We might have debugging messages here that we need to get rid of.
        // End of the component loader mock.
    }
}
