@mod @mod_subsection
Feature: Teacher can only add subsection when certain conditions are met
  In order to limit subsections
  As an teacher
  I need to create subsections only when possible

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | numsections | initsections |
      | Course 1 | C1        | 0        | 5           | 1            |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

  @javascript
  Scenario: We cannot add subsections when maxsections is reached
    Given the following config values are set as admin:
      | maxsections | 10 | moodlecourse |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I click on add content dropdown action
    And I click on "Subsection" "core_course > Add content dropdown action"
    When the following config values are set as admin:
      | maxsections | 4 | moodlecourse |
    And I am on "Course 1" course homepage
    And I should see "You have reached the maximum number of sections allowed for a course."

  @javascript
  Scenario: The add subsection link is disabled when maxsections is reached
    Given the following config values are set as admin:
      | maxsections | 6 | moodlecourse |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I click on add content dropdown action
    And the "class" attribute of "Subsection" "core_course > Add content dropdown action" should not contain "disabled"
    And I click on "Subsection" "core_course > Add content dropdown action"
    And I click on add content dropdown action
    And the "class" attribute of "Subsection" "core_course > Add content dropdown action" should contain "disabled"
    And I should see "You have reached the maximum number of sections allowed for a course."

  @javascript
  Scenario: When subsection is disabled, we should not see the add subsection link
    Given I disable "subsection" "mod" plugin
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I should see "Add an activity or resource" in the "General" "section"
