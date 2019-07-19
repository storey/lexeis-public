Feature: I am able to view and update lemma status as an editor

# Tests based on the default Thucydides lexicon info as of June 17/2019.
# These may not be relevant after changes

# Inconsistencies
Scenario: Lemmata with no references
  When I am logged in as an editor
  And I open the user page
  When I click "Lexicon Inconsistencies" under "Editor Tools"
  Then I see a page with three links for lexicon inconsistencies
  When I click "Lemmata with no references in the text"
  And I wait for the page to resolve
  Then I see a list of the lemmata with zero references
  When I click "ἄγνυμι"
  Then I see the entry page for ἄγνυμι
  And it has 0 occurrences

Scenario: Nonexistent Lemmata
  When I am logged in as an editor
  And I open the user page
  When I click "Lexicon Inconsistencies" under "Editor Tools"
  Then I see a page with three links for lexicon inconsistencies
  When I click "Lemmata referenced by the text that do not exist"
  And I wait for the page to resolve
  Then I see a list of the lemmata referenced by the text
  And there are no lemmata in this list

Scenario: Lemmata with invalid references
  When I am logged in as an editor
  And I open the user page
  When I click "Lexicon Inconsistencies" under "Editor Tools"
  Then I see a page with three links for lexicon inconsistencies
  When I click "Articles that reference text locations in which the lemma does not appear"
  And I wait for the page to resolve
  Then I see a list of the lemmata with invalid references
  And there are two items
  When I click "παραπλέω"
  Then I see the entry page for παραπλέω
