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

/**
 * Behat custom steps and configuration for mod_bigbluebuttonbn.
 *
 * @package   mod_bigbluebuttonbn
 * @category  test
 * @copyright 2021 Andrew Lyons <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use mod_bigbluebuttonbn\instance;
use mod_bigbluebuttonbn\local\config;
use mod_bigbluebuttonbn\test\subplugins_test_helper_trait;
use Moodle\BehatExtension\Exception\SkippedException;
require_once(__DIR__ . '../../../classes/test/subplugins_test_helper_trait.php');
/**
 * Behat custom steps and configuration for mod_bigbluebuttonbn.
 *
 * @package   mod_bigbluebuttonbn
 * @copyright 2021 Andrew Lyons <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_bigbluebuttonbn extends behat_base {
    use subplugins_test_helper_trait;

    /**
     * @var array List of installed subplugins.
     */
    protected $installedsubplugins = [];

    /**
     * BeforeScenario hook to reset the remote testpoint.
     *
     * @BeforeScenario @mod_bigbluebuttonbn
     *
     * @param BeforeScenarioScope $scope
     */
    public function before_scenario(BeforeScenarioScope $scope) {
        if (defined('TEST_MOD_BIGBLUEBUTTONBN_MOCK_SERVER')) {
            $this->send_mock_request('backoffice/reset');
        }
        // Fields are empty by default which causes tests to fail.
        set_config('bigbluebuttonbn_server_url', config::DEFAULT_SERVER_URL);
        set_config('bigbluebuttonbn_shared_secret', config::DEFAULT_SHARED_SECRET);
    }

    /**
     * Check that the TEST_MOD_BIGBLUEBUTTONBN_MOCK_SERVER is defined, so we can connect to the mock server.
     *
     * @Given /^a BigBlueButton mock server is configured$/
     */
    public function mock_is_configured(): void {
        if (!defined('TEST_MOD_BIGBLUEBUTTONBN_MOCK_SERVER')) {
            throw new SkippedException(
                'The TEST_MOD_BIGBLUEBUTTONBN_MOCK_SERVER constant must be defined to run mod_bigbluebuttonbn tests'
            );
        }

        set_config('bigbluebuttonbn_server_url', TEST_MOD_BIGBLUEBUTTONBN_MOCK_SERVER);
    }

    /**
     * Return the list of exact named selectors.
     *
     * @return array
     */
    public static function get_exact_named_selectors(): array {
        return [
            new behat_component_named_selector('Meeting field', [
                <<<XPATH
    .//*[@data-identifier=%locator%]
XPATH
            ], false),
        ];
    }

    /**
     * Retrieve the mock server URL from the TEST_MOD_BIGBLUEBUTTONBN_MOCK_SERVER definition
     *
     * @param string $endpoint
     * @param array $params
     * @return moodle_url
     */
    public static function get_mocked_server_url(string $endpoint = '', array $params = []): moodle_url {
        return new moodle_url(TEST_MOD_BIGBLUEBUTTONBN_MOCK_SERVER . '/' . $endpoint, $params);
    }

    /**
     * Send a query to the mock server
     *
     * @param string $endpoint
     * @param array $params
     */
    protected function send_mock_request(string $endpoint, array $params = []): void {
        $url = $this->get_mocked_server_url($endpoint, $params);

        $curl = new \curl();
        $curl->get($url->out_omit_querystring(), $url->params());
    }

    /**
     * Convert page names to URLs for steps like 'When I am on the "[page name]" page'.
     *
     * Recognised page names are:
     * | None so far!      |                                                              |
     *
     * @param string $page name of the page, with the component name removed e.g. 'Admin notification'.
     * @return moodle_url the corresponding URL.
     * @throws Exception with a meaningful error message if the specified page cannot be found.
     */
    protected function resolve_page_url(string $page): moodle_url {
        throw new Exception("Unrecognised page type '{$page}'.");
    }

    /**
     * Convert page names to URLs for steps like 'When I am on the "[identifier]" "[page type]" page'.
     *
     * Recognised page names are:
     * | pagetype | name meaning     | description                    |
     * | Index    | BBB Course Index | The bbb index page (index.php) |
     *
     * @param string $type identifies which type of page this is, e.g. 'Indez'.
     * @param string $identifier identifies the particular page, e.g. 'Mathematics 101'.
     * @return moodle_url the corresponding URL.
     * @throws Exception with a meaningful error message if the specified page cannot be found.
     */
    protected function resolve_page_instance_url(string $type, string $identifier): moodle_url {
        switch ($type) {
            case 'Index':
                $this->get_course_id($identifier);
                return new moodle_url('/mod/bigbluebuttonbn/index.php', [
                    'id' => $this->get_course_id($identifier),
                ]);
            case 'BigblueButtonBN Guest':
                $cm = $this->get_cm_by_activity_name('bigbluebuttonbn', $identifier);
                $instance = instance::get_from_cmid($cm->id);
                $url = $instance->get_guest_access_url();
                // We have to make sure we set the password. It makes it then easy to submit the form with the right password.
                $url->param('password', $instance->get_guest_access_password());
                return $url;
            default:
                throw new Exception("Unrecognised page type '{$type}'.");
        }
    }

    /**
     * Get course id from its identifier (shortname or fullname or idnumber)
     *
     * @param string $identifier
     * @return int
     */
    protected function get_course_id(string $identifier): int {
        global $DB;

        return $DB->get_field_select(
            'course',
            'id',
            "shortname = :shortname OR fullname = :fullname OR idnumber = :idnumber",
            [
                'shortname' => $identifier,
                'fullname' => $identifier,
                'idnumber' => $identifier,
            ],
            MUST_EXIST
        );
    }

    /**
     * Trigger a recording ready notification on BBB side
     *
     * @Given the BigBlueButtonBN server has sent recording ready notifications
     */
    public function trigger_recording_ready_notification(): void {
        $this->send_mock_request('backoffice/sendRecordingReadyNotifications', [
                'secret' => \mod_bigbluebuttonbn\local\config::DEFAULT_SHARED_SECRET,
            ]
        );
    }

    /**
     * Trigger a meeting event on BBB side
     *
     * @Given /^the BigBlueButtonBN server has received the following events from user "(?P<element_string>(?:[^"]|\\")*)":$/
     * @param string $username
     * @param TableNode $data
     */
    public function trigger_meeting_event(string $username, TableNode $data): void {
        global $DB;
        $user = core_user::get_user_by_username($username);
        $rows = $data->getHash();
        foreach ($rows as $elementdata) {
            $instanceid = $DB->get_field('bigbluebuttonbn', 'id', [
                'name' => $elementdata['instancename'],
            ]);
            $instance = \mod_bigbluebuttonbn\instance::get_from_instanceid($instanceid);
            $this->send_mock_request('backoffice/addMeetingEvent', [
                    'secret' => \mod_bigbluebuttonbn\local\config::DEFAULT_SHARED_SECRET,
                    'meetingID' => $instance->get_meeting_id(),
                    'attendeeID' => $user->id,
                    'attendeeName' => fullname($user),
                    'eventType' => $elementdata['eventtype'],
                    'eventData' => $elementdata['eventdata'] ?? '',
                ]
            );
        }
    }
    /**
     * Send all events received for this meeting back to moodle
     *
     * @Given /^the BigBlueButtonBN activity "(?P<element_string>(?:[^"]|\\")*)" has sent recording all its events$/
     * @param string $instancename
     */
    public function trigger_all_events(string $instancename): void {
        global $DB;

        $instanceid = $DB->get_field('bigbluebuttonbn', 'id', [
            'name' => $instancename,
        ]);
        $instance = \mod_bigbluebuttonbn\instance::get_from_instanceid($instanceid);
        $this->send_mock_request('backoffice/sendAllEvents', [
                'meetingID' => $instance->get_meeting_id(),
                'sendQuery' => true
            ]
        );
    }

    /**
     * Install the simple subplugin
     *
     * Important note here. Originally we had a step that was installing the plugin, however
     * because of race condition (mainly javascript calls), the hack to the core_component was
     * randomly lost due to the component cache being cleared. So we have to install the plugin before
     * any interaction with the site.
     * @BeforeScenario @with_bbbext_simple
     */
    public function install_simple_subplugin() {
        $this->setup_fake_plugin("simple");
        $mockedcomponent = new ReflectionClass(core_component::class);
        $mockedplugintypes = $mockedcomponent->getProperty('plugintypes');
        $mockedplugintypes->setValue(null, null);
        $init = $mockedcomponent->getMethod('init');
        $init->invoke(null);
        // I enable the plugin.
        $manager = core_plugin_manager::resolve_plugininfo_class(\mod_bigbluebuttonbn\extension::BBB_EXTENSION_PLUGIN_NAME);
        $manager::enable_plugin("simple", true);
    }

    /**
     * Uninstall the simple subplugin
     *
     * @AfterScenario @with_bbbext_simple
     */
    public function uninstall_simple_subplugin() {
        $this->uninstall_fake_plugin("simple");
    }

    /**
     * Deletes a recording by name by clicking the delete icon and confirming in modal.
     *
     * @param string $name The name of the recording to delete.
     * @When /^I delete the recording "(?P<name>[^"]+)"$/
     */
    public function i_delete_the_recording($name) {
        $xpath = "//div[contains(@class,'row')][.//*[contains(text(), \"$name\")]]";
        $container = $this->find('xpath', $xpath);

        if (!$container) {
            throw new ElementNotFoundException($this->getSession(), 'recording row', 'xpath', $xpath);
        }

        // Wait for the delete link to be visible.
        $deletelink = $this->spin(function() use ($container) {
            $link = $container->find('css', "a[data-action='delete']");
            return ($link && $link->isVisible()) ? $link : false;
        }, false, 5, new \Exception("Delete link not visible"));

        // Attempt to click (fallback to JS).
        try {
            $deletelink->click();
        } catch (\Exception $e) {
            $this->getSession()->executeScript("arguments[0].click();", [$deletelink]);
        }

        // Wait for modal.
        $this->ensure_element_exists('css', '.modal-content');
        $this->getSession()->wait(500, "document.querySelector('.modal-content') !== null");

        // Find OK button by data-action.
        $okbutton = $this->find('css', '.modal-content button[data-action="save"]');
        if (!$okbutton || !$okbutton->isVisible()) {
            throw new ElementNotFoundException($this->getSession(), 'OK button', 'css', 'button[data-action="save"]');
        }

        // Click the OK button to confirm deletion.
        try {
            $okbutton->click();
        } catch (\Exception $e) {
            $this->getSession()->executeScript("arguments[0].click();", [$okbutton]);
        }

        // Optional: wait briefly for deletion to complete.
        $this->getSession()->wait(500);
    }

    /**
     * Click the import icon for a recording by name.
     *
     * @param string $recordingname The name of the recording to import.
     * @When /^I import the recording "(?P<recordingname>[^"]+)"$/
     */
    public function i_import_the_recording($recordingname) {
        // Find the row/container with the recording name.
        $row = $this->find(
            'xpath',
            "//div[contains(@class, 'row') or contains(@class, 'd-flex')][.//*[contains(text(), \"$recordingname\")]]"
        );

        if (!$row) {
            throw new ElementNotFoundException($this->getSession(), 'recording row', 'xpath', $recordingname);
        }

        // Find the import icon inside that row.
        $importicon = $row->find('css', "a.action-icon[data-action='import']");
        if (!$importicon || !$importicon->isVisible()) {
            throw new ElementNotFoundException($this->getSession(), 'import icon', 'css', "a.action-icon[data-action='import']");
        }

        // Click it (JS fallback included).
        try {
            $importicon->click();
        } catch (\Exception $e) {
            $this->getSession()->executeScript("arguments[0].click();", [$importicon]);
        }

        // Optional wait for import to be processed.
        $this->getSession()->wait(300);
    }

    /**
     * Ensures a CSS element exists before continuing.
     *
     * @param string $type The type of selector (e.g. css, xpath).
     * @param string $selector The actual selector value.
     * @throws ElementNotFoundException If the element is not found after retries.
     */
    protected function ensure_element_exists($type, $selector) {
        for ($i = 0; $i < 5; $i++) {
            $element = $this->getSession()->getPage()->find($type, $selector);
            if ($element) {
                return;
            }
            $this->getSession()->wait(200);
        }

        throw new ElementNotFoundException($this->getSession(), 'element', $type, $selector);
    }

    /**
     * Rename the recording name via inplace editing.
     *
     * @param string $oldname The current name of the recording.
     * @param string $newname The new name to set.
     * @When /^I update the recording name from "(?P<oldname_string>[^"]+)" to "(?P<newname_string>[^"]+)"$/
     */
    public function update_recording_name_from_to(string $oldname, string $newname): void {
        $container = $this->find_recording_row_by_text($oldname);
        $this->update_inplace_editable($container, 'a[title="Edit name"]', $newname);
    }

    /**
     * Rename the recording description via inplace editing.
     *
     * @param string $olddesc The current description of the recording.
     * @param string $newdesc The new description to set.
     * @When /^I update the recording description from "(?P<olddesc_string>[^"]+)" to "(?P<newdesc_string>[^"]+)"$/
     */
    public function update_recording_description_from_to(string $olddesc, string $newdesc): void {
        $container = $this->find_recording_row_by_text($olddesc);
        $this->update_inplace_editable($container, 'a[title="Edit description"]', $newdesc);
    }

    /**
     * Locate a recording row by text content (name or description).
     *
     * @param string $text Text to locate inside the recording row.
     * @return NodeElement
     */
    private function find_recording_row_by_text(string $text): NodeElement {
        $xpath = "//div[contains(@class,'row')][.//*[contains(text(), \"$text\")]]";
        $container = $this->find('xpath', $xpath);

        if (!$container) {
            throw new ElementNotFoundException($this->getSession(), 'recording row', 'xpath', $xpath);
        }

        return $container;
    }

    /**
     * Generic helper to update an inplace editable field (name or description).
     *
     * @param NodeElement $container Container element to scope the search.
     * @param string $editselector CSS selector for the edit icon.
     * @param string $newvalue The new value to set.
     */
    private function update_inplace_editable(NodeElement $container, string $editselector, string $newvalue): void {
        $editlink = $container->find('css', $editselector);
        if (!$editlink) {
            throw new ElementNotFoundException($this->getSession(), 'edit link', 'css', $editselector);
        }

        $editlink->click();
        $this->ensure_element_exists('css', 'span.inplaceeditable input[type="text"]');

        $input = $this->find('css', 'span.inplaceeditable input[type="text"]');
        $input->setValue($newvalue);

        $browser = $this->get_current_browser();
        if ($browser === 'chrome') {
            $input->keyPress(13);
        } else {
            $this->getSession()->executeScript("
                if (arguments[0] && typeof arguments[0].blur === 'function') {
                    arguments[0].blur();
                }
            ", [$input]);
        }

        $this->getSession()->wait(500);
    }

    /**
     * Detects the current browser name (e.g. chrome, firefox).
     *
     * @return string Lowercase browser name, or empty string if unknown.
     */
    private function get_current_browser(): string {
        $driver = $this->getSession()->getDriver();

        if (method_exists($driver, 'getCapabilities')) {
            $caps = $driver->getCapabilities();
            if (!empty($caps['browserName'])) {
                return strtolower($caps['browserName']);
            }
        }

        return '';
    }
}
