@mod @mod_bigbluebuttonbn @core_form @course
Feature: Manage and list recordings
  As a user I am able to import existing recording into another bigbluebutton activity

  Background:  Make sure that import recording is enabled and course, activities and recording exists
    Given a BigBlueButton mock server is configured
    And the following config values are set as admin:
      | bigbluebuttonbn_importrecordings_enabled | 1 |
      | bigbluebuttonbn_importrecordings_from_deleted_enabled | 1 |
    And I enable "bigbluebuttonbn" "mod" plugin
    And the following "courses" exist:
      | fullname      | shortname | category |
      | Test Course 1 | C1        | 0        |
      | Test Course 2 | C2        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | User      | 1        | user1@example.com |
    And the following "activities" exist:
      | activity        | name            | intro                              | course | idnumber         | type | recordings_imported |
      | bigbluebuttonbn | RoomRecordings  | Test Room Recording description    | C1     | bigbluebuttonbn1 | 0    | 0                   |
      | bigbluebuttonbn | RoomRecordings1 | Test Recordings description 1      | C2     | bigbluebuttonbn2 | 0    | 1                   |
      | bigbluebuttonbn | RecordingOnly   | Test Recordings only description 1 | C2     | bigbluebuttonbn3 | 2    | 1                   |
    And the following "mod_bigbluebuttonbn > meeting" exists:
      | activity | RoomRecordings |
    And the following "mod_bigbluebuttonbn > recordings" exist:
      | bigbluebuttonbn | name        | status |
      | RoomRecordings  | Recording 1 | 3      |
      | RoomRecordings  | Recording 2 | 3      |

  @javascript
  Scenario Outline: I can import recordings into a BigBlueButton activity
    When I am on the "<instancename>" "bigbluebuttonbn activity" page logged in as "admin"
    Then I should see "<instancename>"
    And I click on "Import recording links" "button"
    And I select "Test Course 1 (C1)" from the "sourcecourseid" singleselect
    And I select "RoomRecordings" from the "sourcebn" singleselect
    And I import the recording "Recording 1"
    And I import the recording "Recording 2"
    And I click on "Go back" "button"
    Then I should see "Recording 1"
    And I should see "Recording 2"
    Examples:
      | instancename    | existence    |
      | RecordingOnly   | should exist |
      | RoomRecordings1 | should exist |

  @javascript
  Scenario: I can delete an imported recording and see it again in the import list
    When I am on the "RoomRecordings1" "bigbluebuttonbn activity" page logged in as "admin"
    And I change window size to "large"
    And I click on "Import recording links" "button"
    And I select "Test Course 1 (C1)" from the "sourcecourseid" singleselect
    And I select "RoomRecordings" from the "sourcebn" singleselect
    And I import the recording "Recording 1"
    And I import the recording "Recording 2"
    Then I should not see "Recording 1"
    And I should not see "Recording 2"
    And I click on "Go back" "button"
    Then I should see "Recording 1"
    And I should see "Recording 2"
    And I delete the recording "Recording 1"
    Then I should not see "Recording 1"
    And I click on "Import recording links" "button"
    And I select "Test Course 1 (C1)" from the "sourcecourseid" singleselect
    And I select "RoomRecordings" from the "sourcebn" singleselect
    And I should see "Recording 1"

  @javascript @runonly
  Scenario: I can import recordings from a deleted activity and re-import them after deletion
    Given I log in as "admin"
    When I am on "Test Course 1" course homepage with editing mode on
    And I delete "RoomRecordings" activity
    And I run all adhoc tasks
    And I am on the "RoomRecordings1" "bigbluebuttonbn activity" page logged in as "admin"
    And I click on "Import recording links" "button"
    And I select "Recordings from deleted activities" from the "sourcecourseid" singleselect
    Then I should see "Recording 1"
    And I should see "Recording 2"
    And I import the recording "Recording 1"
    And I import the recording "Recording 2"
    And I click on "Go back" "button"
    Then I should see "Recording 1"
    And I delete the recording "Recording 1"
    And I wait until the page is ready
    But I should not see "Recording 1"
    And I click on "Import recording links" "button"
    And I select "Recordings from deleted activities" from the "sourcecourseid" singleselect
    And I should see "Recording 1"
    But I should not see "Recording 2"

  Scenario: I check that when I disable Import recording feature the import recording link button should not be shown
    Given I log in as "admin"
    And the following config values are set as admin:
      | bigbluebuttonbn_importrecordings_enabled | 0 |
    When I am on the "RoomRecordings1" "bigbluebuttonbn activity" page logged in as "admin"
    Then I should not see "Import recording links"
