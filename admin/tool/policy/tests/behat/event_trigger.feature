@tool @tool_policy
Feature: Accepting and reviewing acceptance later through the banner
  As a user I can accept all policies, accept only mandatory policies and review my choice later.

  Background:
    Given the following config values are set as admin:
      | sitepolicyhandler | tool_policy |
    # This is required for now to prevent the overflow region affecting the action menus.
    And the following policies exist:
      | name                | revision | content    | summary     | status | optional | audience | agreementstyle |
      | This cookies policy |          | full text3 | short text3 | active | 1        | all      | 1              |

  @javascript
  Scenario: A change in policy acceptance will trigger an event with the policies accepted
    Given I log in as "admin"
    And I visit "/admin/tool/policy/tests/fixtures/displaypolicies.php"
    And I should see "If you want to continue browsing this website, you need to agree to some of our policies."
    And I click on "Show settings" "button"
    And I should see "This cookies policy"
    Then I click on "input[name=\"this-cookies-policy\"]" "css_element"
    Then I click on "Save my choices" "button"
    Then I wait until the page is ready
    Then I should see "Event triggered"
    And I should see "This cookies policy: yes (policies_accepted)"
    Then I click on "Show Policies Banner" "button"
    Then I wait until the page is ready
    Then I click on "input[name=\"this-cookies-policy\"]" "css_element"
    Then I click on "Save my choices" "button"
    And I should see "This cookies policy: no (policies_accepted)"

  @javascript
  Scenario: When policy is accepted it still send a message on page reload with current status
    Given I log in as "admin"
    And I visit "/admin/tool/policy/tests/fixtures/displaypolicies.php"
    And I should see "If you want to continue browsing this website, you need to agree to some of our policies."
    And I click on "Show settings" "button"
    And I should see "This cookies policy"
    Then I click on "input[name=\"this-cookies-policy\"]" "css_element"
    Then I click on "Save my choices" "button"
    Then I wait until the page is ready
    Then I should see "Event triggered"
    And I should see "This cookies policy: yes (policies_accepted)"
    Then I reload the page
    And I should see "This cookies policy: yes (current_status)"