Feature: I am able to add, edit, and delete lemmata

# These should be done in order; they rely on state from the previous tests
# This is based on the Thucydides lexicon

Scenario: Add a Lemma
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Lemmata" under "Editor Tools"
  Then I see the Manage Lemmata page
  When I click "Add a new lemma"
  Then I see the Add New Lemma page
  When I enter "βουλεύω" as the lemma
  And I enter "Prophecy" as the short definition
  And I select "Noun" as the part of speech
  And I click "Create Lemma"
  Then an error appears telling me "Lemma βουλεύω already exists."
  When I enter "παραμαντέω" as the lemma
  And I click "Create Lemma"
  Then I see the entry page for παραμαντέω
  And there are 0 occurrences
  When I click "more" in the top section
  Then I see the Root is None
  And I see the Compound Parts is None
  When I click "more" in the middle section
  There is no semantic group
  When I click "edit"
  Then I go to the edit page for παραμαντέω

Scenario: Edit a Lemma
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Lemmata" under "Editor Tools"
  Then I see the Manage Lemmata page
  When I search for "paramantew"
  Then I go to the edit page for παραμαντέω
  When I enter "βουλεύω" as the lemma
  And I click "Make Changes to Lemma"
  Then an error appears telling me "Lemma βουλεύω already exists."
  When I enter "περιμαντέω" as the lemma
  And I enter "Read Omens" as the short definition
  And I select "Verb" as the part of speech
  And I select "περί" as the compound
  And I select "μάντις" as the root
  And I select "Inquiring Knowing and Understanding" as the semantic group
  And I select "Has Illustration"
  And I enter "A Caption" as the caption
  And I select some image file as the illustration
  And I enter "Source 1", newline, "Source 2" as the bibliography
  And I click "Make Changes to Lemma"
  Then I see the entry page for περιμαντέω
  And the short definition is "Read Omens"
  When I click "more" in the top section
  Then I see μάντις as the root
  And I see περί as the compound part
  When I click "more" in the middle section
  Then the semantic group is Inquiring Knowing and Understanding
  And I see the illustration uploaded with caption "A Caption"
  And the Bibliography's first bullet point is "Source 1"
  And the Bibliography's second bullet point is "Source 1"
  And there are 0 occurrences
  When I click root μάντις
  And I see a list of lemmata with this root in alphabetical order
  And I see περιμαντέω in the list


Scenario: Delete a Lemma
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Lemmata" under "Editor Tools"
  Then I see the Manage Lemmata page
  When I search for "περιμαντέω"
  Then I go to the edit page for περιμαντέω
  When I click "Delete περιμαντέω"
  Then a confirmation popup appears
  When I click "Cancel"
  Then I go to the edit page for περιμαντέω
  When I click "Delete περιμαντέω"
  Then a confirmation popup appears
  When I click "Yes"
  Then I see the Manage Lemmata page

Scenario: Search for Deleted Lemma
  When I open the search page
  And I search for "περιμαντέω"
  Then I see an error message saying no entries match

Scenario: Delete a Lemma With Occurrences
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Lemmata" under "Editor Tools"
  Then I see the Manage Lemmata page
  When I search for "Ἀθῆναι"
  Then I go to the edit page for περιμαντέω
  When I click "Delete Ἀθῆναι"
  Then a confirmation popup appears
  When I click "Yes"
  Then I see an error that I cannot delete a lemma that appears in the text, with some of the text locations.

Scenario: Undo a Delete
  When I am logged in as an administrator
  And I open the user page
  And I click "Change Log" under "Admin Tools"
  Then I see an entry with change type "Delete a lemma" and context "περιμαντέω"
  When I click "Undo" for the entry with change type "Delete a lemma" and context "περιμαντέω"
  Then a popup opens
  And I see information on the change
  And I see an Undo Change button
  When I click "Undo Change"
  Then a confirmation message appears
  When I click "Okay"
  Then the popup closes
  When I click "Undo" for the entry with change type "Delete a lemma" and context "περιμαντέω"
  Then a popup opens
  And I see information on the change
  And I see an Undo Change button
  When I click "Undo Change"
  Then a message that says "This lemma has already been un-deleted."
  When I click "Okay"
  Then the popup closes

# TODO: undeleting when a new lemma with the same name exists should be blocked too

# Todo: More specific
Scenario: Articles Change Log
  When I am logged in as an administrator
  And I open the user page
  And I click "Change Log" under "Admin Tools"
  Then I see a log of changes matching what was done for these tests
