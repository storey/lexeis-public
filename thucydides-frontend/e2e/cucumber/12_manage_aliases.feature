Feature: I am able to add, edit, and delete lemmata

# These should be done in order; they rely on state from the previous tests
# This is based on the Thucydides lexicon
# If you are testing manually, some of the numbers might be slightly different

# This assumes the lemma "περιμαντέω" exists from manage-lemmata.feature

# Note that this alias makes no sense, but we'll delete it later
Scenario: Add an Alias
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Aliases" under "Editor Tools"
  Then I see the Manage Aliases page
  And there are 3 aliases visible
  When I click "Add a new alias"
  Then I see the Add New Alias page
  When I enter "περιμεντέω" as the alias
  And I enter "ουλεύω" as the lemma
  And I click "Create Alias"
  Then an error appears telling me "Lemma ουλεύω does not exist."
  And I enter "βουλεύω" as the lemma
  And I click "Create Alias"
  Then I see the Manage Aliases page
  And there are 4 aliases visible
  And one of the aliases is "περιμεντέω" -> "βουλεύω"

Scenario: New Alias is Searchable
  When I open the search page
  And I search for "περιμεντέω"
  Then I see one entry option
  And the entry option says "See βουλεύω"
  When I click the first entry item
  Then I see the entry page for βουλεύω

Scenario: Edit an Alias
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Aliases" under "Editor Tools"
  Then I see the Manage Aliases page
  And there are 4 aliases visible
  When I click alias "περιμεντέω" in the list
  Then I see an edit/delete page for alias περιμεντέω
  When I enter "ουλεύω" as the lemma
  And I click "Make Changes to Alias"
  Then an error appears telling me "Lemma ουλεύω does not exist."
  When I enter "περιμαντάω" as the alias
  And I enter "περιμαντέω" as the lemma
  And I click "Make Changes to Alias"
  Then I see the Manage Aliases page

Scenario: Edited Alias is Searchable
  When I open the search page
  And I search for "περιμαντάω"
  Then I see one entry option
  And the entry option says "See περιμαντέω"
  When I click the first entry item
  Then I see the entry page for περιμαντέω

Scenario: Old Alias is No Longer Searchable
  When I open the search page
  And I search for "περιμεντέω"
  Then I see an error message saying no entries match

Scenario: Delete Lemma with Aliases
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Lemmata" under "Editor Tools"
  Then I see the Manage Lemmata page
  When I search for "περιμαντέω"
  Then I go to the edit page for περιμαντέω
  When I click "Delete περιμαντέω"
  Then a confirmation popup appears
  When I click "Yes"
  Then I see an error that I cannot delete a lemma with aliases, with the alias περιμαντάω.

# Add/edit alias to one that already exists
Scenario: Add/Edit an Alias to Overlap with Existing Alias
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Aliases" under "Editor Tools"
  When I click "Add a new alias"
  Then I see the Add New Alias page
  When I enter "περιμαντάω" as the alias
  And I enter "βουλεύω" as the lemma
  And I click "Create Alias"
  Then an error appears telling me "Alias περιμαντάω already exists."
  And I enter "βωλεύω" as the alias
  And I click "Create Alias"
  Then I see the Manage Aliases page
  And there are 5 aliases visible
  And one of the aliases is "βωλεύω" -> "βουλεύω"
  When I click alias "βωλεύω" in the list
  Then I see an edit/delete page for alias βωλεύω
  When I enter "περιμαντάω" as the alias
  And I click "Make Changes to Alias"
  Then an error appears telling me "Alias περιμαντάω already exists."

Scenario: Delete Alias
  When I am logged in as an editor
  And I open the user page
  When I click "Manage Aliases" under "Editor Tools"
  Then I see the Manage Aliases page
  And there are 5 aliases visible
  When I click alias "περιμαντάω" in the list
  Then I see an edit/delete page for alias περιμαντάω
  When I click "Delete περιμαντάω"
  Then a confirmation popup opens
  When I click "Cancel" in the popup
  Then the popup closes
  And I see an edit/delete page for alias περιμαντέω
  When I click "Delete περιμαντέω"
  Then a confirmation popup opens
  When I click "Yes" in the popup
  Then the popup closes
  And I see the Manage Aliases page
  And there are 4 aliases visible

Scenario: Searching for Deleted Alias
  When I open the search page
  And I search for "περιμαντάω"
  Then I see an error message saying no entries match

# Todo: More specific
Scenario: Articles Change Log
  When I am logged in as an administrator
  And I open the user page
  And I click "Change Log" under "Admin Tools"
  Then I see a log of changes matching what was done for these tests
