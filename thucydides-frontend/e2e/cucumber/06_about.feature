Feature: I am able to view information about the website on the about page.

# Visiting about page
Scenario: Visit the about page from main button
  When I open the main page
  And I click the "About" button
  Then I see the about page

# Visiting about page from nav
Scenario: Visit the about page from the nav bar
  When I open the main page
  And I click the "About" nav entry
  Then I see the about page
  When I click the screencast link
  Then I see a video of how to use the website
