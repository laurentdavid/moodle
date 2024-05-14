@javascript @theme_boost @screenshot_comparison
Feature: When upgrading Boost theme to Boostrap 5, we need to keep the same look and feel.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | initsections |
      | Course 1 | C1        | 1           |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |

  Scenario: I should see the course page with the same look and feel
    Given I am on the "Course 1" course page logged in as "teacher1"
    And I hover over the "[data-for='sectiontoggler']" "css" in the "Section 1" "section"
    Then the screenshot "course" should match the reference screenshot

