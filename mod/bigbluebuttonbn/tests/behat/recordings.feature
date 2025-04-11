@mod @mod_bigbluebuttonbn
Feature: The recording can be managed through the room page
  As a user I am able to see the relevant recording for a given bigbluebutton activity and modify its parameters

  Background:  Make sure that import recording is enabled and course, activities and recording exists
    Given a BigBlueButton mock server is configured
    And I enable "bigbluebuttonbn" "mod" plugin
    And the following "courses" exist:
      | fullname      | shortname | category |
      | Test Course 1 | C1        | 0        |
      | Test Course 2 | C2        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | User      | 1        | user1@example.com |
    And the following "activities" exist:
      | activity        | name           | intro                           | course | idnumber         | type | recordings_imported |
      | bigbluebuttonbn | RoomRecordings | Test Room Recording description | C1     | bigbluebuttonbn1 | 0    | 0                   |
    And the following "mod_bigbluebuttonbn > meeting" exists:
      | activity | RoomRecordings |
    And the following "mod_bigbluebuttonbn > recordings" exist:
      | bigbluebuttonbn | name        | description   | status |
      | RoomRecordings  | Recording 1 | Description 1 | 2      |
      | RoomRecordings  | Recording 2 | Description 2 | 3      |
      | RoomRecordings  | Recording 3 | Description 3 | 0      |
      | RoomRecordings  | Recording 4 | Description 4 | 1      |

  @javascript
  Scenario: I can see the recordings related to an activity
    Given I am on the "RoomRecordings" "bigbluebuttonbn activity" page logged in as admin
    Then I should see "Recording 1"
    And I should see "Recording 2"
    And I should not see "Recording 3"
    And I should not see "Recording 4"
    # Recording 3 will be fetched and metadata will be present so, we will see it.
    When the BigBlueButtonBN server has sent recording ready notifications
    And I run the scheduled task "mod_bigbluebuttonbn\task\check_pending_recordings"
    And I reload the page
    Then I should see "Recording 1"
    And I should see "Recording 2"
    And I should see "Recording 3"
    And I should not see "Recording 4"

  @javascript
  Scenario: I can rename the recording
    Given I am on the "RoomRecordings" "bigbluebuttonbn activity" page logged in as admin
    Then I should see "Recording 1"
    When I update the recording name from "Recording 1" to "Recording with an updated name 1"
    And I press enter in the recording name input
    Then I should see "Recording with an updated name 1"
    And I reload the page
    Then I should see "Recording with an updated name 1"

  @javascript
  Scenario: I can set a new description for this recording
    Given I am on the "RoomRecordings" "bigbluebuttonbn activity" page logged in as admin
    Then I should see "Recording 1"
    When I update the recording description from "Description 1" to "This is a new recording description 1"
    And I press enter in the recording name input
    Then I should see "This is a new recording description 1"
    And I reload the page
    Then I should see "This is a new recording description 1"

  @javascript
  Scenario: I can delete a recording
    Given I am on the "RoomRecordings" "bigbluebuttonbn activity" page logged in as admin
    And I change window size to "large"
    And I should see "Recording 2"
    Then I should see "Recording 1"
    When I delete the recording "Recording 1"
    Then I should not see "Recording 1"
    And I should see "Recording 2"
    And I reload the page
    Then I should not see "Recording 1"
    And I should see "Recording 2"
