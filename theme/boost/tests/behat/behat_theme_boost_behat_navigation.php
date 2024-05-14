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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.
// For that reason, we can't even rely on $CFG->admin being available here.

require_once(__DIR__ . '/../../../../lib/tests/behat/behat_navigation.php');

use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Step definitions related to the navigation in the Boost theme.
 *
 * @package    theme_boost
 * @category   test
 * @copyright  2021 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_theme_boost_behat_navigation extends behat_navigation {
    /**
     * @var BeforeStepScope|null $currentscope store current scope.
     */
    private ?BeforeStepScope $currentscope = null;

    /**
     * Checks whether a node is active in the navbar.
     *
     * @override i should see :name is active in navigation
     *
     * @param string $element The name of the nav elemnent to look for.
     * @return void
     * @throws ElementNotFoundException
     */
    public function i_should_see_is_active_in_navigation($element) {
        $this->execute("behat_general::assert_element_contains_text",
            [$element, '.navbar .nav-link.active', 'css_element']);
    }

    /**
     * Checks whether a node is active in the secondary nav.
     *
     * @Given i should see :name is active in secondary navigation
     * @param string $element The name of the nav elemnent to look for.
     * @return void
     * @throws ElementNotFoundException
     */
    public function i_should_see_is_active_in_secondary_navigation($element) {
        $this->execute("behat_general::assert_element_contains_text",
            [$element, '.secondary-navigation .nav-link.active', 'css_element']);
    }

    /**
     * Checks whether the language selector menu is present in the navbar.
     *
     * @Given language selector menu should exist in the navbar
     * @Given language selector menu should :not exist in the navbar
     *
     * @param string|null $not Instructs to checks whether the element does not exist in the user menu, if defined
     * @return void
     * @throws ElementNotFoundException
     */
    public function lang_menu_should_exist($not = null) {
        $callfunction = is_null($not) ? 'should_exist' : 'should_not_exist';
        $this->execute("behat_general::{$callfunction}", [$this->get_lang_menu_xpath(), 'xpath_element']);
    }

    /**
     * Return the xpath for the language selector menu element.
     *
     * @return string The xpath
     */
    protected function get_lang_menu_xpath() {
        return "//nav[contains(concat(' ', @class, ' '), ' navbar ')]" .
            "//div[contains(concat(' ', @class, ' '),  ' langmenu ')]" .
            "//div[contains(concat(' ', @class, ' '), ' dropdown-menu ')]";
    }

    /**
     * Checks whether an item exists in the language selector menu.
     *
     * @Given :itemtext :selectortype should exist in the language selector menu
     * @Given :itemtext :selectortype should :not exist in the language selector menu
     *
     * @param string $itemtext The menu item to find
     * @param string $selectortype The selector type
     * @param string|null $not Instructs to checks whether the element does not exist in the user menu, if defined
     * @return void
     * @throws ElementNotFoundException
     */
    public function should_exist_in_lang_menu($itemtext, $selectortype, $not = null) {
        $callfunction = is_null($not) ? 'should_exist_in_the' : 'should_not_exist_in_the';
        $this->execute("behat_general::{$callfunction}",
            [$itemtext, $selectortype, $this->get_lang_menu_xpath(), 'xpath_element']);
    }

    /**
     * Compare the screenshot with the reference screenshot. If the reference screenshot
     * does not exist, create it, save it and pass the test (this is used to have an init step).
     *
     * @Then the screenshot :screenshotname should match the reference screenshot
     * @param string $screenshotname
     */
    public function the_screeshot_should_match(string $screenshotname) {
        if (!$this->has_tag('screenshot_comparison')) {
            $this->skipScenario('Screenshot comparison is not enabled for this scenario.');
        }
        if (!file_exists($this->get_screenshot_reference_dir())) {
            mkdir($this->get_screenshot_reference_dir(), 0777, true);
        }
        $filename = $this->get_screenshot_filename($screenshotname);
        $referencedir = $this->get_screenshot_reference_dir();

        if (file_exists($referencedir . $filename)) {
            [$differencepercentage, $diffimagepath] = $this->compare_screenshots($filename);
            //if (!empty($CFG->behat_faildump_path)) {
            //    $behathookcontext = $this->currentscope->getEnvironment()->getContext('behat_hooks');
            //    $behathookcontextReflector = new ReflectionClass($behathookcontext);
            //    $behathookcontextReflector->getMethod('get_faildump_filename')->setAccessible(true);
            //    [$failedumpdir, $filename] =
            //        $behathookcontextReflector->getMethod('get_faildump_filename')->invoke($behathookcontext);
            //    copy($diffimagepath, $failedumpdir . '/diff_' . $filename);
            //}
            unlink($diffimagepath);
            if ($differencepercentage > 0.1) {
                throw new Exception("The screenshot $filename does not match the reference screenshot. Difference: $differencepercentage%");
            }
            unlink($diffimagepath);
        } else {
            // Create a reference screenshot.
            $this->saveScreenshot($filename, $referencedir);
        }
    }

    /**
     * Compare the screenshot with the reference screenshot.
     *
     * @param $filename
     * @return array
     */
    protected function compare_screenshots($filename): array {
        $temp = make_temp_directory('behat_compare_screenshots');
        $this->saveScreenshot($filename, $temp);
        $originalscreenshot = $this->get_screenshot_reference_dir() . $filename;
        $newscreenshot = $temp . '/' . $filename;
        $originalimage = imagecreatefrompng($originalscreenshot);
        $newimage = imagecreatefrompng($newscreenshot);
        $origwidth = imagesx($originalimage);
        $origheight = imagesy($originalimage);
        $newwidth = imagesx($newimage);
        $newheight = imagesy($newimage);

        if ($origwidth !== $newwidth || $origheight !== $newheight) {
            debugging("The screenshots have different dimensions.");
            return 100;
        }

        $diffimage = imagecreatetruecolor($origwidth, $origheight);

        $red = imagecolorallocate($diffimage, 255, 0, 0);
        $totalpixels = $origwidth * $origheight;
        $differentpixels = 0;

        for ($x = 0; $x < $origwidth; $x++) {
            for ($y = 0; $y < $origheight; $y++) {
                $rgb1 = imagecolorat($originalimage, $x, $y);
                $rgb2 = imagecolorat($newimage, $x, $y);

                if ($rgb1 !== $rgb2) {
                    imagesetpixel($diffimage, $x, $y, $red);
                    $differentpixels++;
                } else {
                    $color = imagecolorsforindex($originalimage, $rgb1);
                    $colorindex = imagecolorallocate($diffimage, $color['red'], $color['green'], $color['blue']);
                    imagesetpixel($diffimage, $x, $y, $colorindex);
                }
            }
        }

        $diffimagepath = $temp . '/diff_' . $filename;
        imagepng($diffimage, $diffimagepath);
        imagedestroy($originalimage);
        imagedestroy($newimage);
        imagedestroy($diffimage);
        unlink($newscreenshot);
        $differencepercentage = ($differentpixels / $totalpixels) * 100;
        return [$differencepercentage, $diffimagepath];
    }

    /**
     * Get screenshot reference directory. If it does not exist, create it.
     * @return string
     */
    protected function get_screenshot_reference_dir() {
        $expecteddir = __DIR__. '/../fixtures/screenshots_reference/';
        if (!file_exists($expecteddir)) {
            mkdir($expecteddir, 0777, true);
        }
        return $expecteddir;
    }

    /**
     * Get screenshot filename for current step with the
     * @param string $suffix
     * @return string
     */
    protected function get_screenshot_filename(string $suffix, string $filetype = 'png'): string {
        // Very similar to the way we generate the faildump file name.
        // The scenario title + the step text.
        // We want a i-am-the-scenario-title_i-am-the-failed-step_<suffix>.$filetype format.
        $filename = $this->currentscope->getFeature()->getTitle();

        // As file name is limited to 255 characters. Leaving 5 chars for line number and 4 chars for the file.
        // extension as we allow .png for images and .html for DOM contents.
        $filenamelen = 50 - strlen($suffix);

        // Suffix suite name to faildump file, if it's not default suite.
        $suitename = $this->currentscope->getSuite()->getName();
        if ($suitename != 'default') {
            $suitename = '_' . $suitename;
            $filenamelen = $filenamelen - strlen($suitename);
        } else {
            // No need to append suite name for default.
            $suitename = '';
        }

        $filename = preg_replace('/([^a-zA-Z0-9\_]+)/', '-', $filename);
        return strtolower(substr($filename, 0, $filenamelen) . $suitename . '_' . $suffix . '.' . $filetype);
    }

    /**
     * Hook to store the current scope for later use.
     *
     * @param BeforeStepScope $scope
     * @BeforeStep
     */
    public function store_scope(BeforeStepScope $scope) {
        $this->currentscope = $scope;
    }
}
