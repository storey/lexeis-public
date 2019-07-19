Feature: I am able to add, edit, and delete roots

# These should be done in order; they rely on state from the previous tests
# This is based on the Thucydides lexicon

Scenario: Add a Root
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Roots" under "Editor Tools"
  Then I see the Manage Roots page
  When I click "Add a new root"
  Then I see the Add New Root page
  When I enter "ἀγαθός" as the root
  And I enter "Test" as the description
  And I click "Create Root"
  Then an error appears telling me "Root ἀγαθός already exists."
  When I enter "ἀάρος" as the root
  And I click "Create Root"
  Then I see a page for the root ἀάρος
  And there is a message saying there are no lemmata in this group
  When I click "edit"
  Then I see the edit root page for ἀάρος

Scenario: Edit a Root
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Roots" under "Editor Tools"
  Then I see the Manage Roots page
  And I see 20 roots
  When I click "Next"
  Then I see 20 roots
  When I click "Prev"
  Then I see 20 roots
  When I select "ἀάρος" from the list of roots
  Then I see the edit root page for ἀάρος
  When I enter "ἀγαθός" as the root
  And I click "Make Changes to Root"
  Then an error appears telling me "Compound ἀγαθός already exists."
  When I enter "Ἰθάκα" as the root
  And I enter "Ithaca" as the description
  And I click "Make Changes to Root"
  Then I see a page for the root Ἰθάκα
  And the description is "Ithaca"

Scenario: Choose Root
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Lemmata" under "Editor Tools"
  Then I see the Manage Lemmata page
  When I click "Add a new lemma"
  Then I see the Add New Lemma page
  And I see "Ἰθακα" as an option for the roots

Scenario: Delete a Root
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Roots" under "Editor Tools"
  Then I see the Manage Roots page
  When I select "Ἰθάκα" from the list of roots (page 22)
  Then I see the edit root page for Ἰθάκα
  When I click "Delete Ἰθάκα"
  Then a confirmation popup appears
  When I click "Cancel"
  Then I go to the edit page for Ἰθάκα
  When I click "Delete Ἰθάκα"
  Then a confirmation popup appears
  When I click "Yes"
  Then I see the Manage Roots page

Scenario: Delete a Root With Occurrences
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Roots" under "Editor Tools"
  Then I see the Manage Roots page
  When I select "ἀγάπη" from the list of roots
  Then I see the edit root page for ἀγάπη
  When I click "Delete ἀγάπη"
  Then a confirmation popup appears
  When I click "Yes"
  Then I see an error that I cannot delete a root linked to a lemma
  And the error message lists the lemmata this root is linked to

Scenario: Search for Deleted Root
  When I open the root page for Ἰθακα (/rootGroup/Ἰθάκα)
  Then I see an error message saying there is no root "Ἰθάκα"

Scenario: Undo a Delete
  When I am logged in as an administrator
  And I open the user page
  And I click "Change Log" under "Admin Tools"
  Then I see an entry with change type "Delete a root" and context "Ἰθάκα"
  When I click "Undo" for the entry with change type "Delete a root" and context "Ἰθάκα"
  Then a popup opens
  And I see information on the change
  And I see an Undo Change button
  When I click "Undo Change"
  Then a confirmation message appears
  When I click "Okay"
  Then the popup closes
  When I click "Undo" for the entry with change type "Delete a root" and context "Ἰθάκα"
  Then a popup opens
  And I see information on the change
  And I see an Undo Change button
  When I click "Undo Change"
  Then a message that says "This root has already been un-deleted."
  When I click "Okay"
  Then the popup closes

# TODO: undeleting when a new root with the same name exists should be blocked too

# Todo: More specific
Scenario: Articles Change Log
  When I am logged in as an administrator
  And I open the user page
  And I click "Change Log" under "Admin Tools"
  Then I see a log of changes matching what was done for these tests
