Feature: I am able to download a zip of the lexicon

# Tests based on the default Thucydides lexicon

# Create backup
Scenario: Create Backup
  When I am logged in as an administrator
  And I open the user page
  And I click "Manage Backups" under "Admin Tools"
  Then I see a page of backups
  When I click "Create New Backup"
  And I wait a moment
  Then I see a confirmation message

# Make a changes that can be confirmed to be be reverted
Scenario: Make Changes to Database
  When I am logged in as an administrator
  And I open the user page
  And I click "Manage Lemmata" under "Editor Tools"
  Then I see the Manage Lemmata page
  When I click "Add a new lemma"
  Then I see the Add New Lemma page
  When I enter "ββακυπ" as the lemma
  And I enter "Backup" as the short definition
  And I select "Noun" as the part of speech
  And I click "Create Lemma"
  Then I see the entry page for ββακυπ
  When I open the word list page
  And I click "Β" in the top list
  Then "Ββ" is an option in the second list
  When I open the user page
  And I click "Manage Text" under "Editor Tools"
  Then I see the Manage Text page
  When I enter section 1.1.1 in the "Choose Section" field
  And Click "Go"
  Then I see the Edit Section page for 1.1.1
  When I click the "Edit" link for Token Θουκυδίδης
  Then I see the Edit Word page for Θουκυδίδης (5044329)
  When I enter "ββακυπ" as the lemma
  And I click "Edit Word"
  Then I see the Edit Section page for 1.1.1
  When I open the text page
  Then I see section 1.1.1
  When I click "Θουκυδίδης"
  Then I see the entry page for ββακυπ

# Load database
  When I am logged in as an administrator
  And I open the user page
  And I click "Manage Backups" under "Admin Tools"
  Then I see a page of backups
  When I click the most recent backup
  Then a popup opens
  When I click "Cancel" in the popup
  Then the popup closes
  Then I see a page of backups
  When I click the most recent backup
  Then a popup opens
  When I click "Restore" in the popup
  And wait a few moments
  Then I see a success message
  When I click "Okay" in the popup
  Then the popup closes
  And I see the recompile texts page
  When I click "Recompile"
  And wait for loading to finish
  Then I see a confirmation message
  When I open the search page
  And I search for "ββακυπ"
  Then I see an error message saying no entries match
  When I open the word list page
  And I click "Β" in the top list
  Then "Ββ" is NOT an option in the second list
  When I open the text page
  Then I see section 1.1.1
  When I click "Θουκυδίδης"
  Then I see the entry page for Θουκυδίδης
