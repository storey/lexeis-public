Feature: I am able to report issues

Scenario: Report an issue while not logged in
  When I am not logged in
  And I open the main page
  Then I see "Report An Issue" in the bottom right corner
  When I click "Report An Issue"
  Then I see a popup with three fields
  And there are no errors
  When I enter "a" as my email address
  And click the "what page is this issue on" area
  Then I see an error message for the email
  When I enter "lexeisdioiketes@gmail.com" as my email address
  And I click the "what page is this issue on" area
  Then I see do not see an error message for the email
  When I enter "Test Issue" as the issue
  And I click Submit
  Then I see a confirmation page
  When I click the "X" in the top right
  Then I see the main page with no popup

Scenario: Report an issue while logged in
  When I am logged in as a user
  And I open the search page
  And I search for "isthmi"
  Then I see the entry page for ἵστημι
  And I see "Report An Issue" in the bottom right corner
  When I click "Report An Issue"
  Then I see a popup with two fields
  And there are no errors
  And the "what page is the issue on" has the value "/entry/ἵστημι"
  When I click the "what is the issue" area
  And I enter nothing
  And I click the issue text area
  Then I see an error message for the issue text area
  When I enter "Another Test Issue" as the issue
  And I click the "what page is this issue on" area
  Then I see do not see an error message for the issue text area
  When I click Submit
  Then I see a confirmation page
  When I click "Submit another issue"
  Then a popup with two fields appears
  When I click the "X" in the top right
  Then I see the main page with no popup

Scenario: Report an issue through the user page
  When I am logged in as a user
  And I open the user page
  And I click "Report an Issue" under "User Actions"
  Then I see a page with two fields
  When I enter "/wordList/Λ" for the page input
  And I enter "Third Test" as the issue
  And I click the "Submit" button
  Then I see a confirmation page

# Note: other three must have been done first
Scenario: View and resolve reported issues
  When I am logged in as an editor
  And I open the user page
  And I click "Reported Issues" under "Editor Tools"
  Then I see at least three unresolved issues in red, with names Test Issue, Another Test, and Third Test
  When I click "View" for the second item
  Then I see a page with the unresolved issue
  And I see the issue location is "/entry/ἵστημι"
  And I see the comment is "Another Test Issue"
  When I enter "Issue Resolved" in the comment section
  And I click "Resolve Issue"
  Then I see a page with the issue resolved.
  And I see the resolution comment "Issue Resolved"
  When I click "Back to issues list"
  Then I no longer see the "Another Test Issue" issue
  When I click "Yes" for "Show resolved issues?"
  Then I see the "Another Test Issue" issue in Green.
