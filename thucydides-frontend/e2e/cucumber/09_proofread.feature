Feature: I am able to view and update lemma status as an editor

# These should be done in order; they rely on state from the previous one
# This is based on the Thucydides lexicon
# If you are testing manually, some of the numbers might be slightly different

# This will be slightly different depending on whether articles.feature has been
# done already; my assumption is that is has.

# Assign articles draft status
Scenario: Assign articles draft status
  When I am logged in as an editor
  And I open the entry page for ἀναπλέω
  Then I see an orange status display circle
  When I click the status display circle
  Then I see a popup showing the review status for the entry
  When I click 'Update status to "Draft"'
  Then the popup closes
  Then I see a yellow status display circle
  When I open the entry page for ἐπιπλέω
  Then I see an orange status display circle
  When I click the status display circle
  Then I see a popup showing the review status for the entry
  When I click 'Update status to "Draft"'
  Then the popup closes
  Then I see a yellow status display circle
  When I open the entry page for ἐκπλέω
  Then I see an orange status display circle
  When I click the status display circle
  Then I see a popup showing the review status for the entry
  When I click 'Update status to "Draft"'
  Then the popup closes
  Then I see a yellow status display circle

# Articles to proofread
Scenario: View Articles to Proofread and Update From Draft Status
  When I am logged in as an editor
  And I open the user page
  Then the first number next to the "Proofread Lexicon Entries" link is at least 3
  When I click "Proofread Lexicon Entries" under "Editor Tools"
  Then I see a page on entries to review
  And there are at least 3 entries to be proofread, in alphabetical order
  And there are 0 entries to be finalized
  When I click "ἀναπλέω" in the list of entries to be proofread
  Then I see the entry page for ἀναπλέω in a new tab
  Then I see a yellow status display circle
  When I click the status display circle
  Then I see a popup showing the review status for the entry
  When I click 'Update status to "Awaiting Final proof"'
  Then the popup closes
  Then I see a light green status display circle

Scenario: View Articles to Proofread and Update From Draft Status
  When I am logged in as an editor
  And I open the user page
  Then the first number next to the "Proofread Lexicon Entries" link is at least 2
  Then the second number next to the "Proofread Lexicon Entries" link is 1
  When I click "Proofread Lexicon Entries" under "Editor Tools"
  Then I see a page on entries to review
  And there are at least 2 entries to be proofread
  And there is 1 entry to be finalized
  When I click "ἀναπλέω" in the list of entries to be finalized
  Then I see the entry page for ἀναπλέω in a new tab
  Then I see a light green status display circle
  When I click the status display circle
  Then I see a popup showing the review status for the entry
  When I click 'Update status to "Final"'
  Then the popup closes
  Then I see a deep green status display circle
  When I open the user page
  Then the second number next to the "Proofread Lexicon Entries" link is 0
  When I click "Proofread Lexicon Entries" under "Editor Tools"
  Then I see a page on entries to review
  And there are at least 2 entries to be proofread
  And there are 0 entries to be finalized

# Todo: More specific
Scenario: Articles Change Log
  When I am logged in as an administrator
  And I open the user page
  And I click "Change Log" under "Admin Tools"
  Then I see a log of changes matching what was done for these tests
