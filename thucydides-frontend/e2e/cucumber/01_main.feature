Feature: Main page provides access to all features

# All the pieces are correctly displayed on the front page
Scenario: I visit the main page
  When I open the main page
  Then I see the title "A Thucydidean Lexicon"
  Then I see a large button which links to the search interface
  Then I see a large button which links to the word list
  Then I see a large button which links to a view of the text
  Then I see a large button which links to the about page

# When not logged in
Scenario: I visit the main page while not logged in
  When I am not logged in
  And I open the main page
  Then I see four nav items

# When logged in
Scenario: I visit the main page while logged in
  When I am logged in
  And I open the main page
  Then I see five nav items
  The fifth nav item is my username
