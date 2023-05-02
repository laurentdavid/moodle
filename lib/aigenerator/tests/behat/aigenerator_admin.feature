@core @core_aigenerator
Feature: I setup the AI generator subsystem.

  Background:
    Given I log in as "admin"

  Scenario: The AI Generator subsystem is disabled by default
    Given I navigate to "Advanced features" in site administration
    And the field "Enable AI Generator subsystem" matches value "0"

  @javascript
  Scenario Outline: As the AI Generator subsystem is disabled/enabled I should not see/should see the AI Generator menu in the Plugins page
    Given I navigate to "Advanced features" in site administration
    When I set the field "Enable AI Generator subsystem" to "<status>"
    And I press "Save changes"
    And I select "Site administration" from primary navigation
    And I select "Plugins" from secondary navigation
    Then I <visible> see "Manage AI Generator providers"

    Examples:
      | status | visible    |
      | 1      | should     |
      | 0      | should not |
