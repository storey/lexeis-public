Feature: I am able to add, edit, and delete semantic groups

# These should be done in order; they rely on state from the previous tests
# This is based on the Thucydides lexicon

Scenario: Add a Semantic Group
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Semantic Groups" under "Editor Tools"
  Then I see the Manage Semantic Groups page
  When I click "Add a new semantic group"
  Then I see the Add New Semantic Group page
  When I enter "Judgment and Morality" as the semantic group
  And I enter "A little of this" as the description
  And I click "Create Semantic Group"
  Then an error appears telling me 'Semantic Group "Judgment and Morality" already exists.'
  When I enter "Friendship" as the semantic group
  And I click "Create Semantic Group"
  Then I see a page for the semantic group Friendship
  And there is a message saying there are no lemmata in this group
  When I click "edit"
  Then I see the edit semantic group page for 52 (Friendship)

Scenario: Edit a Semantic Group
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Semantic Groups" under "Editor Tools"
  Then I see the Manage Semantic Groups page
  And I see 20 semantic groups
  When I click "Next"
  Then I see 20 semantic groups
  When I click "Prev"
  Then I see 20 semantic groups
  When I select "Friendship" from the list of semantic groups
  Then I see the edit semantic group page for Friendship
  When I enter "Judgment and Morality" as the semantic group
  And I click "Make Changes to Semantic Group"
  Then an error appears telling me 'Semantic Group "Judgment and Morality" already exists.'
  When I enter "Enmity" as the semantic group
  And I select Style 1 as the style
  And I enter "More of this" as the description
  And I click "Make Changes to Semantic Group"
  Then I see a page for the semantic group 52 (Enmity)
  And the description is "More of this"

Scenario: Choose Semantic Group
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Lemmata" under "Editor Tools"
  Then I see the Manage Lemmata page
  When I click "Add a new lemma"
  Then I see the Add New Lemma page
  And I see "Enmity" as an option for the semantic groups

Scenario: Delete a Semantic Group
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Semantic Groups" under "Editor Tools"
  Then I see the Manage Semantic Groups page
  When I select "Enmity" from the list of semantic groups
  Then I see the edit semantic group page for ἰντρά
  When I click "Delete Enmity"
  Then a confirmation popup appears
  When I click "Cancel"
  Then I go to the edit page for Enmity
  When I click "Delete Enmity"
  Then a confirmation popup appears
  When I click "Yes"
  Then I see the Manage Semantic Groups page

Scenario: Delete a Semantic Group With Occurrences
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Semantic Groups" under "Editor Tools"
  Then I see the Manage Semantic Groups page
  When I select "Choice" from the list of semantic groups
  Then I see the edit semantic group page for Choice
  When I click "Delete Choice"
  Then a confirmation popup appears
  When I click "Yes"
  Then I see an error that I cannot delete a semantic group linked to lemmata
  And the error message lists some of the lemmata that this semantic group is linked to

Scenario: Search for Deleted Semantic Group
  When I open the semantic group page for 52 (/semanticGroup/52)
  Then I see an error message saying there is no semantic group "52"

Scenario: Undo a Delete
  When I am logged in as an administrator
  And I open the user page
  And I click "Change Log" under "Admin Tools"
  Then I see an entry with change type "Delete a semantic group" and context "Enmity"
  When I click "Undo" for the entry with change type "Delete a semantic group" and context "Enmity"
  Then a popup opens
  And I see information on the change
  And I see an Undo Change button
  When I click "Undo Change"
  Then a confirmation message appears
  When I click "Okay"
  Then the popup closes
  When I click "Undo" for the entry with change type "Delete a semantic group" and context "Enmity"
  Then a popup opens
  And I see information on the change
  And I see an Undo Change button
  When I click "Undo Change"
  Then a message that says "This semantic group has already been un-deleted."
  When I click "Okay"
  Then the popup closes

# TODO: undeleting when a new semantic group with the same name exists should be blocked too

# Todo: More specific
Scenario: Articles Change Log
  When I am logged in as an administrator
  And I open the user page
  And I click "Change Log" under "Admin Tools"
  Then I see a log of changes matching what was done for these tests
