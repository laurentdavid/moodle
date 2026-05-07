@mod @mod_forum
Feature: Forum discussion navigation with icon buttons
  In order to navigate between forum discussions
  As a user
  I want to use icon-based navigation buttons instead of text links

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | student1 | Student   | One      | student1@example.com  |
      | student2 | Student   | Two      | student2@example.com  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student2 | C1     | student |
    And the following "activity" exists:
      | activity   | forum                    |
      | course     | C1                       |
      | idnumber   | forum1                   |
      | name       | Test Forum               |
      | forcesubscribe | 1                   |
    And the following "mod_forum > discussions" exist:
      | forum  | user     | name              | subject           | message                      |
      | forum1 | student1 | First Discussion  | First Discussion  | Content of first discussion  |
      | forum1 | student1 | Second Discussion | Second Discussion | Content of second discussion |
      | forum1 | student1 | Third Discussion  | Third Discussion  | Content of third discussion  |

  Scenario: Navigation buttons are displayed at top and bottom of discussion and previous button is active when viewing non-first discussion
    Given I am logged in as "student1"
    And I am on the "Test Forum" "forum activity" page
    When I click on "Second Discussion" "link"
    Then I should see "2" node occurrences of type "nav" in the "[data-content='forum-discussion']" "css_element"
    And "Previous discussion: First Discussion" "mod_forum > Discussion navigation link" should exist
    And "Next discussion: Third Discussion" "mod_forum > Discussion navigation link" should exist

  Scenario: Previous button is disabled when viewing first discussion
    Given I am logged in as "student1"
    And I am on the "Test Forum" "forum activity" page
    When I click on "First Discussion" "link"
    Then "Next discussion: Second Discussion" "mod_forum > Discussion navigation link" should exist
    And the "class" attribute of "prev" "mod_forum > Discussion navigation link" should contain "disabled"

  Scenario: Next button is disabled when viewing last discussion
    Given I am logged in as "student1"
    And I am on the "Test Forum" "forum activity" page
    When I click on "Third Discussion" "link"
    Then "Previous discussion: Second Discussion" "mod_forum > Discussion navigation link" should exist
    And the "class" attribute of "next" "mod_forum > Discussion navigation link" should contain "disabled"

  Scenario: Previous button navigates to previous discussion
    Given I am logged in as "student1"
    And I am on the "Test Forum" "forum activity" page
    When I click on "Second Discussion" "link"
    And I click on "Previous discussion: First Discussion" "mod_forum > Discussion navigation link"
    Then I should see "First Discussion" in the "region-main" "region"

  Scenario: Next button navigates to next discussion
    Given I am logged in as "student1"
    And I am on the "Test Forum" "forum activity" page
    When I click on "Second Discussion" "link"
    And I click on "Next discussion: Third Discussion" "mod_forum > Discussion navigation link"
    Then I should see "Third Discussion" in the "region-main" "region"

  Scenario: Navigation is not shown in experimental nested view
    Given I am logged in as "student1"
    And I follow "Preferences" in the user menu
    And I click on "Forum preferences" "link"
    And I set the field "Use experimental nested discussion view" to "Yes"
    And I press "Save changes"
    And I am on the "Test Forum" "forum activity" page
    When I click on "Second Discussion" "link"
    Then "nav.discussion-nav" "css_element" should not exist
