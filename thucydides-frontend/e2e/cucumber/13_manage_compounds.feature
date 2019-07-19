Feature: I am able to add, edit, and delete compounds

# These should be done in order; they rely on state from the previous tests
# This is based on the Thucydides lexicon

Scenario: Add a Compound
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Compound Parts" under "Editor Tools"
  Then I see the Manage Compound Parts page
  When I click "Add a new compound part"
  Then I see the Add New Compound Part page
  When I enter "περί" as the compound part
  And I enter "Above" as the description
  And I click "Create Compound Part"
  Then an error appears telling me "Compound περί already exists."
  When I enter "συπέρ" as the compound part
  And I click "Create Compound Part"
  Then I see a page for the compound συπέρ
  And there is a message saying there are no lemmata in this group
  When I click "edit"
  Then I see the edit compound page for συπέρ

Scenario: Edit a Compound Part
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Compound Parts" under "Editor Tools"
  Then I see the Manage Compound Parts page
  And I see 20 compound parts
  When I click "Next"
  Then I see 1 compound part
  When I click "Prev"
  Then I see 20 compound parts
  When I select "συπέρ" from the list of compound parts
  Then I see the edit compound page for συπέρ
  When I enter "περί" as the compound
  And I click "Make Changes to Compound Part"
  Then an error appears telling me "Compound περί already exists."
  When I enter "ἰντρά" as the compound
  And I enter "Between" as the description
  And I click "Make Changes to Compound Part"
  Then I see a page for the compound ἰντρά
  And the description is "Between"

Scenario: Choose Compound Part
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Lemmata" under "Editor Tools"
  Then I see the Manage Lemmata page
  When I click "Add a new lemma"
  Then I see the Add New Lemma page
  And I see "ἰντρά" as an option for the compound parts

Scenario: Delete a Compound Part
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Compound Parts" under "Editor Tools"
  Then I see the Manage Compound Parts page
  When I select "ἰντρά" from the list of compound parts
  Then I see the edit compound page for ἰντρά
  When I click "Delete ἰντρά"
  Then a confirmation popup appears
  When I click "Cancel"
  Then I go to the edit page for ἰντρά
  When I click "Delete ἰντρά"
  Then a confirmation popup appears
  When I click "Yes"
  Then I see the Manage Compound Parts page

Scenario: Delete a Compound Part With Occurrences
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Compound Parts" under "Editor Tools"
  Then I see the Manage Compound Parts page
  When I select "ἀντί" from the list of compound parts
  Then I see the edit compound page for ἀντί
  When I click "Delete ἀντί"
  Then a confirmation popup appears
  When I click "Yes"
  Then I see an error that I cannot delete a compound part linked to a lemma
  And the error message lists some of the lemmata that this compound part is linked to

Scenario: Search for Deleted Compound Part
  When I open the compound page for ἰντρά (/compound/ἰντρά)
  Then I see an error message saying there is no compound part "ἰντρά"

Scenario: Undo a Delete
  When I am logged in as an administrator
  And I open the user page
  And I click "Change Log" under "Admin Tools"
  Then I see an entry with change type "Delete a compound" and context "ἰντρά"
  When I click "Undo" for the entry with change type "Delete a compound" and context "ἰντρά"
  Then a popup opens
  And I see information on the change
  And I see an Undo Change button
  When I click "Undo Change"
  Then a confirmation message appears
  When I click "Okay"
  Then the popup closes
  When I click "Undo" for the entry with change type "Delete a compound" and context "ἰντρά"
  Then a popup opens
  And I see information on the change
  And I see an Undo Change button
  When I click "Undo Change"
  Then a message that says "This compound has already been un-deleted."
  When I click "Okay"
  Then the popup closes

# TODO: undeleting when a new compound with the same name exists should be blocked too

# Todo: More specific
Scenario: Articles Change Log
  When I am logged in as an administrator
  And I open the user page
  And I click "Change Log" under "Admin Tools"
  Then I see a log of changes matching what was done for these tests
