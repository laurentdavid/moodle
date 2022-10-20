@mod @mod_data @javascript @_file_upload
Feature: Users can use predefined presets
  In order to use presets
  As a user
  I need to select an existing preset

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | name                | intro           | course | idnumber |
      | data     | Mountain landscapes | introduction... | C1     | data1    |
    And the following "mod_data > presets" exist:
      | database | name           | description                 |
      | data1    | Saved preset 1 | The preset1 has description |
      | data1    | Saved preset 2 |                             |

  Scenario: Teacher can use presets from field page on an empty database
    Given I am on the "Mountain landscapes" "data activity" page logged in as teacher1
    And I follow "Fields"
    And I set the field "Fields tertiary navigation" to "Use a preset"
    And I click on "fullname" "radio" in the "Image gallery" "table_row"
    And the "Use preset" "button" should be enabled
    Then I click on "Use preset" "button"
    Then I should not see "Field mappings"
    # I am on the index page.
    Then I should see "No entries yet"
    And I should see "Preset applied."
    And I should see "Fields created: 3"

  Scenario: Teacher can use a preset from field page on a database with existing fields
    Given the following "mod_data > fields" exist:
      | database | type | name            | description            |
      | data1    | text | Test field name | Test field description |
    Given I am on the "Mountain landscapes" "data activity" page logged in as teacher1
    And I follow "Fields"
    And I set the field "Fields tertiary navigation" to "Use a preset"
    And I click on "fullname" "radio" in the "Image gallery" "table_row"
    And the "Use preset" "button" should be enabled
    Then I click on "Use preset" "button"
    Then I click on "Map fields" "button"
    Then I click on "Continue" "button"
    # I am on the index page.
    Then I should see "Add entry"
    And I should see "Preset applied."
    And I should see "Fields created: 3"

  Scenario: Teacher can use a preset from field page on a database with existing fields
  using the default mapping
    Given the following "mod_data > fields" exist:
      | database | type | name            | description            |
      | data1    | text | Test field name | Test field description |
    Given I am on the "Mountain landscapes" "data activity" page logged in as teacher1
    And I follow "Fields"
    And I set the field "Fields tertiary navigation" to "Use a preset"
    And I click on "fullname" "radio" in the "Image gallery" "table_row"
    And the "Use preset" "button" should be enabled
    Then I click on "Use preset" "button"
    Then I click on "Apply preset" "button"
    Then I should not see "Field mappings"
    # I am on the index page.
    Then I should see "No entries yet"
    And I should see "Preset applied."
    And I should see "Fields created: 3"

  Scenario: Teacher can use a preset from field page on a database with existing entries
    And the following "mod_data > fields" exist:
      | database | type | name   | description            |
      | data1    | text | field1 | Test field description |
    And the following "mod_data > templates" exist:
      | database | name            |
      | data1    | singletemplate  |
      | data1    | listtemplate    |
      | data1    | addtemplate     |
      | data1    | asearchtemplate |
      | data1    | rsstemplate     |
    And the following "mod_data > entries" exist:
      | database | field1          |
      | data1    | Student entry 1 |
    Given I am on the "Mountain landscapes" "data activity" page logged in as teacher1
    And I follow "Fields"
    And I set the field "Fields tertiary navigation" to "Use a preset"
    And I click on "fullname" "radio" in the "Image gallery" "table_row"
    And the "Use preset" "button" should be enabled
    Then I click on "Use preset" "button"
    Then I click on "Map fields" "button"
    Then I click on "Continue" "button"
    # I am on the index page.
    Then I should see "Add entry"
    And I should see "Preset applied."
    And I should see "Fields created: 3"

  Scenario: If Teacher use the same preset twice then there is a warning saying that no changes have been made
    Given I am on the "Mountain landscapes" "data activity" page logged in as teacher1
    And I follow "Presets"
    And I click on "fullname" "radio" in the "Image gallery" "table_row"
    And the "Use preset" "button" should be enabled
    Then I click on "Use preset" "button"
    And I should not see "Field mappings"
    And I should see "No entries yet"
    Then I follow "Presets"
    And I click on "fullname" "radio" in the "Image gallery" "table_row"
    And the "Use preset" "button" should be enabled
    Then I click on "Use preset" "button"
    And I should see "Preset applied."
    And I should not see "Fields created"

  Scenario: If Teacher use another preset then the previous fields are removed
    Given I am on the "Mountain landscapes" "data activity" page logged in as teacher1
    And I follow "Presets"
    And I click on "fullname" "radio" in the "Image gallery" "table_row"
    And the "Use preset" "button" should be enabled
    Then I click on "Use preset" "button"
    And I should not see "Field mappings"
    And I should see "No entries yet"
    Then I follow "Presets"
    And I click on "fullname" "radio" in the "Journal" "table_row"
    And the "Use preset" "button" should be enabled
    Then I click on "Use preset" "button"
    Then I click on "Apply preset" "button"
    And I should see "Preset applied."
    And I should see "Fields created: 2"
    Then I follow "Fields"
    And I should see "Title"
    And I should see "Content"
    And I should not see "image"

  Scenario: If Teacher use another preset then the previous fields are removed
    Given I am on the "Mountain landscapes" "data activity" page logged in as teacher1
    And I follow "Presets"
    And I click on "fullname" "radio" in the "Image gallery" "table_row"
    And the "Use preset" "button" should be enabled
    Then I click on "Use preset" "button"
    And I should not see "Field mappings"
    And I should see "No entries yet"
    Then I follow "Presets"
    And I click on "fullname" "radio" in the "Journal" "table_row"
    And the "Use preset" "button" should be enabled
    Then I click on "Use preset" "button"
    Then I click on "Apply preset" "button"
    And I should see "Preset applied."
    And I should see "Fields created: 2"
    Then I follow "Fields"
    And I should see "Title"
    And I should see "Content"
    And I should not see "image"


  Scenario: Teacher can use a preset from zero state page on an empty database
    Given I am on the "Mountain landscapes" "data activity" page logged in as teacher1
    And I click on "Use preset" "button"
    And I click on "fullname" "radio" in the "Image gallery" "table_row"
    And the "Use preset" "button" should be enabled
    Then I click on "Use preset" "button"
    Then I should not see "Field mappings"
    # I am on the index page.
    Then I should see "No entries yet"
    And I should see "Preset applied."
    And I should see "Fields created: 3"
