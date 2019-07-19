Feature: I am able to access and use lexicon entries by searching for them.

# Visiting search page
Scenario: Visit the search page from main page
  When I open the main page
  And I click the "Search Entries" button
  Then I see the search page

# Visiting search page from nav
Scenario: Visit the search page from the nav bar
  When I open the main page
  And I click the "Search" nav entry
  Then I see the search page

# Searching functionality
Scenario: Get more info on searching
  When I open the search page
  And I click the "How do I search?" button
  Then I see additional information on how to search
  When I click the "betacode" link
  Then I see a page with information on Betacode

Scenario: Search for a nonexistent entry
  When I open the search page
  And I search for "βεβάντε"
  Then I see an error message saying no entries match

Scenario: Search for an entry with multiple results
  When I open the search page
  And I search for "ειμι"
  Then I see 2 entry options
  And the entry options each have short definitions
  When I click the first entry item
  Then I see the entry page for εἰμί

# Also a search using accented unicode
Scenario: Search for an entry with a single result
  When I open the search page
  And I search for "ἀγαθός"
  Then I see the entry page for ἀγαθός

Scenario: Search using unaccented unicode
  When I open the search page
  And I search for "αγαθος"
  Then I see the entry page for ἀγαθός

Scenario: Search using accented betacode
  When I open the search page
  And I search for "a)gaqo/s"
  Then I see the entry page for ἀγαθός

Scenario: Search using unaccented betacode
  When I open the search page
  And I search for "agaqos"
  Then I see additional information on how to search

Scenario: Search using English approximation
  When I open the search page
  And I search for "khora"
  Then I see the entry page for χώρα

Scenario: Search for an alias
  When I open the search page
  And I search for "πλεῖστος"
  Then I see one entry option
  And the entry option says "See πολύς"
  When I click the first entry item
  Then I see the entry page for πολύς

# Entry data

Scenario: Open lemma info of an entry
  When I open the entry page for ἀγαθός
  Then I see only the lemma name in the top section
  When I click "more" in the top section
  Then I see lemma info like part of speech and frequency in the top section
  When I click "less" in the top section
  Then I see only the lemma name in the top section

Scenario: Open dictionary info of an entry
  When I open the entry page for ἀγαθός
  Then I see only a short definition in the middle section
  When I click "more" in the middle section
  Then I see a full definition in the middle section
  And I see the full definition has an author entry
  And I see a semantic group in the middle section
  When I click "less" in the middle section
  Then I see only a short definition in the middle section

Scenario: Open occurrence info of an entry
  When I open the entry page for ἀγαθός
  Then I see only the number of occurrences in the bottom section
  When I click "show" in the bottom section
  Then I see a list of occurrences ordered by location
  When I click the "context" option
  Then I see a list of occurrences ordered by context
  When I click the "location" option
  Then I see a list of occurrences ordered by location
  When I click "hide" in the bottom section
  Then I see only the number of occurrences in the bottom section

Scenario: Open dictionary info of an entry with an image
  When I open the entry page for ἐπωτίδες
  And I click "more" in the middle section
  Then I see a full definition in the middle section
  And I see an illustration with a caption in the middle section

# Login visibility
Scenario: Edit button invisible when not logged in
  When I am not logged in
  And I open the entry page for ἀγαθός
  Then I do not see an edit button in the top section

Scenario: Edit button invisible when logged in as a user
  When I am logged in as a user
  And I open the entry page for ἀγαθός
  Then I do not see an edit button in the top section

Scenario: Edit button invisible when logged in as a contributor
  When I am logged in as a contributor
  And I open the entry page for ἀγαθός
  Then I do not see an edit button in the top section

Scenario: Edit button and info available when logged in as an editor
  When I am logged in as an editor
  And I open the entry page for ἀγαθός
  Then I see an edit button in the top section
  Then I see a "view as editor" option
  When I click "no" for view as editor
  Then I do not see an edit button in the top section
  When I click "yes" for view as editor
  Then I see a "view as editor" option
  Then I see a status display circle
  When I click the status display circle
  Then I see a popup showing the review status for the entry
  When I click the x in the popup
  Then the popup closes
  When I click "edit"
  Then I go to the edit page for ἀγαθός
