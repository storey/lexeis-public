Feature: I am able to access and use lexicon entries by using the word list.

# Visiting word list page
Scenario: Visit the word list page from main button
  When I open the main page
  And I click the "Word List" button
  Then I see the word list page

# Visiting word list page from nav
Scenario: Visit the word list page from the nav bar
  When I open the main page
  And I click the "Word List" nav entry
  Then I see the word list page

# Word list functionality
Scenario: Access a word using the word list
  When I open the word list page
  Then I see a bar for first letters
  Then I do not see a second bar for first two letters
  When I click "Β" in the first bar
  Then I see a bar for first letters
  Then I see a second bar for first two letters
  When I click "Βι" in the second bar
  Then I see 10 entry options
  And the entry options each have short definitions
  When I click "Βα" in the second bar
  Then I see a pagination with 2 pages
  When I click page 2 in the pagination bar
  Then I see 6 entry options
  When I click the first entry item
  Then I see the entry page for βασανίζω


Scenario: Special two-letter combos
  When I open the word list page
  When I click "Ο" in the first bar
  And I click "Ο" in the second bar
  Then I see an entry for "ὁ"
  When I click "Ω" in the first bar
  And I click "Ωσ" in the second bar
  Then I see an entry for "ὡς"
