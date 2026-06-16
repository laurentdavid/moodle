@core @core_course @core_courseformat @format_topics @format_weeks @format_singleactivity
Feature: Display the course linear navigation
  In order to quickly navigate through the course activities in a linear way
  As a user
  I want to see the course linear navigation when the course format supports it and it is enabled in the course settings

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | s1       | Student   | 1        |
      | t1       | Teacher   | 1        |

  @javascript
  Scenario Outline: As a user I should see the course linear navigation when format allows it and it is enabled
    Given the following "courses" exist:
      | fullname | shortname | format    | enablelinearnav |
      | Course1  | C1        | <format>  | <linearnav>     |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | s1      | C1     | student        |
      | t1      | C1     | editingteacher |
    And the following "activities" exist:
      | activity | name   | course |
      | page     | Page1  | C1     |
    When I am on the "Page1" "page activity" page logged in as "<user>"
    Then the course linear navigation <shouldbevisible>

    Examples:
      | format         | linearnav | user | shouldbevisible       |
      | topics         | 1         | s1   | should be visible     |
      | topics         | 1         | t1   | should be visible     |
      | topics         | 0         | s1   | should not be visible |
      | topics         | 0         | t1   | should not be visible |
      | weeks          | 1         | s1   | should be visible     |
      | weeks          | 1         | t1   | should be visible     |
      | weeks          | 0         | s1   | should not be visible |
      | weeks          | 0         | t1   | should not be visible |
      | singleactivity | 1         | s1   | should not be visible |
      | singleactivity | 1         | t1   | should not be visible |

  @javascript
  Scenario Outline: Clicking Next navigates to the next activity
    Given the following "courses" exist:
      | fullname | shortname | format   | enablelinearnav | numsections |
      | Course1  | C1        | <format> | 1               | 0           |
    And the following "course enrolments" exist:
      | user | course | role    |
      | s1   | C1     | student |
    And the following "activities" exist:
      | activity | name  | course |
      | page     | Page1 | C1     |
      | page     | Page2 | C1     |
    When I am on the "Page1" "page activity" page logged in as "s1"
    And I should not see "Back to course" in the "sticky-footer" "region"
    And I click on "Next" "link" in the "sticky-footer" "region"
    Then I should see "Page2" in the "page-header" "region"
    # The last activity in the course should show "Back to course" to redirect to the course.
    And I click on "Back to course" "link" in the "sticky-footer" "region"
    And I should see "Course1" in the "page-header" "region"

    Examples:
      | format |
      | topics |
      | weeks  |

  @javascript
  Scenario Outline: Clicking Next skips hidden and not available elements
    Given the following "courses" exist:
      | fullname | shortname | format   | enablelinearnav | numsections |
      | Course1  | C1        | <format> | 1               | 0           |
    And the following "course enrolments" exist:
      | user | course | role           |
      | s1   | C1     | student        |
      | t1   | C1     | editingteacher |
    And the following "activities" exist:
      | activity | name  | course | section |
      | page     | Page1 | C1     | 0       |
      | page     | Page2 | C1     | 0       |
      | page     | Page3 | C1     | 0       |
      | page     | Page4 | C1     | 0       |
    And I am on the "Course1" "course" page logged in as "t1"
    And I turn editing mode on
    # Hide Page2 activity
    And I open "Page2" actions menu
    And I click on "Hide" "link" in the "Page2" activity
    # Add a restriction to Page3 that makes it unavailable to students
    And I open "Page3" actions menu
    And I click on "Edit settings" "link" in the "Page3" activity
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Date" "button" in the "Add restriction..." "dialogue"
    And I set the following fields to these values:
      | direction | from |
      | x[year]   | 2035 |
    And I click on "Item name displayed with access restriction information if student doesn't meet this condition • Click to hide" "link"
    And I press "Save and return to course"
    When I am on the "Page1" "page activity" page logged in as "s1"
    And I click on "Next" "link" in the "sticky-footer" "region"
    Then I should see "Page4" in the "page-header" "region"

    Examples:
      | format |
      | topics |
      | weeks  |

  @javascript
  Scenario Outline: Clicking Previous navigates to the previous activity
    Given the following "courses" exist:
      | fullname | shortname | format   | enablelinearnav |
      | Course1  | C1        | <format> | 1               |
    And the following "course enrolments" exist:
      | user | course | role    |
      | s1   | C1     | student |
    And the following "activities" exist:
      | activity | name  | course |
      | page     | Page1 | C1     |
      | page     | Page2 | C1     |
    When I am on the "Page2" "page activity" page logged in as "s1"
    And I click on "Previous" "link" in the "sticky-footer" "region"
    Then I should see "Page1" in the "page-header" "region"
    And I should not see "Previous" in the "sticky-footer" "region"

    Examples:
      | format |
      | topics |
      | weeks  |

  @javascript
  Scenario Outline: Clicking Previous skips hidden and not available elements
    Given the following "courses" exist:
      | fullname | shortname | format   | enablelinearnav | numsections |
      | Course1  | C1        | <format> | 1               | 0           |
    And the following "course enrolments" exist:
      | user | course | role           |
      | s1   | C1     | student        |
      | t1   | C1     | editingteacher |
    And the following "activities" exist:
      | activity | name  | course | section |
      | page     | Page1 | C1     | 0       |
      | page     | Page2 | C1     | 0       |
      | page     | Page3 | C1     | 0       |
      | page     | Page4 | C1     | 0       |
    And I am on the "Course1" "course" page logged in as "t1"
    And I turn editing mode on
    # Hide Page2 activity
    And I open "Page2" actions menu
    And I click on "Hide" "link" in the "Page2" activity
    # Add a restriction to Page3 that makes it unavailable to students
    And I open "Page3" actions menu
    And I click on "Edit settings" "link" in the "Page3" activity
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Date" "button" in the "Add restriction..." "dialogue"
    And I set the following fields to these values:
      | direction | from |
      | x[year]   | 2035 |
    And I click on "Item name displayed with access restriction information if student doesn't meet this condition • Click to hide" "link"
    And I press "Save and return to course"
    When I am on the "Page4" "page activity" page logged in as "s1"
    And I click on "Previous" "link" in the "sticky-footer" "region"
    Then I should see "Page1" in the "page-header" "region"

    Examples:
      | format |
      | topics |
      | weeks  |
