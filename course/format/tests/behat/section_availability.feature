@core @core_courseformat
Feature: Verify section availability interface
  In order to edit the course sections availability
  As a teacher
  I need to be able to see the updated availability information

  Background:
    Given the following "course" exists:
      | fullname     | Course 1 |
      | shortname    | C1       |
      | category     | 0        |
      | numsections  | 3        |
      | initsections | 1        |
    And the following "activities" exist:
      | activity | name              | intro                       | course | idnumber | section |
      | assign   | Activity sample 1 | Test assignment description | C1     | sample1  | 1       |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    Given I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I edit the section "1"
    And I expand all fieldsets
    And I press "Add restriction..."
    And I click on "Date" "button" in the "Add restriction..." "dialogue"
    And I set the field "direction" to "until"
    And I set the field "x[year]" to "2013"
    And I set the field "x[month]" to "March"
    And I press "Add restriction..."
    And I click on "User profile" "button" in the "Add restriction..." "dialogue"
    And I set the field "User profile field" to "Email address"
    And I set the field "Value to compare against" to "email@example.com"
    And I set the field "Method of comparison" to "is equal to"
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: As a teacher I can see the section availability information completely unfolded
    in editing mode and it is folded by default when editing mode is off.
    Given I am on the "Course 1" course page logged in as "teacher1"
    And I switch editing mode on
    # We use the fact that the section the second condition is only visible when expanded
    Then I should see "Email address is email@example.com"
    When I switch editing mode off
    Then I should not see "Email address is email@example.com"

  @javascript
  Scenario: As a a student section should be collapsed by default.
    Given I am on the "Course 1" course page logged in as "teacher1"
    Then I should not see "Email address is email@example.com"
