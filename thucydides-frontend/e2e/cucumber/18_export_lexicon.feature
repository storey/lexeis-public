Feature: I am able to download a zip of the lexicon

# Tests based on the default Thucydides lexicon

Scenario: Lemmata with no references
  When I am logged in as an administrator
  And I open the user page
  And I click "Export Lexicon" under "Admin Tools"
  Then I see a page for exporting information
  When I click "Create Export"
  And I wait for the page to resolve
  Then I see a message saying "Process complete".
  When I click "Download Export"
  Then a zip file of lexicon information is downloaded.
