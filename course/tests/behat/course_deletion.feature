@core @core_course
Feature: Teachers can delete courses with some modules disabled.
  In order to delete a course
  As moodle admin
  I should be able to delete the course even when modules are disabled.

  Background:
    Given the following "categories" exist:
      | name  | category | idnumber |
      | Cat 1 | 0        | CAT1     |
    # We use group mode here to cover all possible case for which we might need to get a cm_info.
    And the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | CAT1     | 1         |

  Scenario: Test deleting a course when course has an assignment module is disabled
    Given the following "activities" exist:
      | activity        | name | intro            | course | idnumber | groupmode |
      | assign          | A1   | Assignment 1     | C1     | ass1     | 1         |
    And I am logged in as "admin"
    And I navigate to "Plugins > Activity modules > Manage activities" in site administration
    And I click on "Disable Assignment" "link"
    And I should see "Assignment disabled"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I click on "Cat 1" "link" in the "course-category-listings" "region"
    And I click on "Delete" "link" in the "course-listing" "region"
    And I should see "Confirm"
    And I press "Delete"
    And I should see "C1 has been completely deleted"
    When I navigate to "Courses > Manage courses and categories" in site administration
    Then I should not see "Course 1"

  Scenario: Test deleting a course when course has a bigbluebutton module is disabled
    Given a BigBlueButton mock server is configured
    And the following "activities" exist:
      | activity        | name | intro            | course | idnumber | groupmode |
      | bigbluebuttonbn | B1   | BigBlueButton B1 | C1     | b1       | 1         |
    And I am logged in as "admin"
    And I navigate to "Plugins > Activity modules > Manage activities" in site administration
    And I click on "Disable Assignment" "link"
    And I should see "Assignment disabled"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I click on "Cat 1" "link" in the "course-category-listings" "region"
    And I click on "Delete" "link" in the "course-listing" "region"
    And I should see "Confirm"
    And I press "Delete"
    And I should see "C1 has been completely deleted"
    When I navigate to "Courses > Manage courses and categories" in site administration
    Then I should not see "Course 1"

  Scenario: Test deleting a course when all modules are enabled
    Given I am logged in as "admin"
    And I navigate to "Courses > Manage courses and categories" in site administration
    And I click on "Cat 1" "link" in the "course-category-listings" "region"
    And I click on "Delete" "link" in the "course-listing" "region"
    And I should see "Confirm"
    And I press "Delete"
    And I should see "C1 has been completely deleted"
    When I navigate to "Courses > Manage courses and categories" in site administration
    Then I should not see "Course 1"
