Feature: I am able to assign, write, manage, and approve article drafts.

# These should be done in order; they rely on state from the previous articles
# This is based on the Thucydides lexicon with no articles written
# If you are testing manually, some of the numbers might be slightly different

# Assign articles to a user
Scenario: Navigating the Assign Articles Page
  When I am logged in as an editor
  And I open the user page
  Then I see a number of unassigned articles next to "Assign Unwritten Articles."
  When I click "Assign Unwritten Articles" under "Editor Tools"
  Then I see the Assign Unwritten Articles page
  And I see 20 unwritten articles
  And the "First" button is disabled
  And the "Prev" button is disabled
  And the "Next" button is enabled
  And the "Last" button is enabled
  When I click the "Next" button
  Then I go to page 1
  And the "First" button is enabled
  And the "Prev" button is enabled
  And the "Next" button is enabled
  And the "Last" button is enabled
  When I click the "Prev" button
  Then I go to page 0
  When I click the "Last" button
  Then I go to the last page
  And the "First" button is enabled
  And the "Prev" button is enabled
  And the "Next" button is disabled
  And the "Last" button is disabled
  When I click the "First" button
  Then I go to page 0

Scenario: Filtering Unwritten Articles
  When I am logged in as an editor
  And I open the user page
  And I click "Assign Unwritten Articles" under "Editor Tools"
  Then I see the Assign Unwritten Articles page
  And I see 20 unwritten articles
  When I select "ἀγαθός" from the Root dropdown
  Then I see 4 unwritten articles
  When I select "Judgement and Morality" from the Semantic Group dropdown
  Then I see 3 unwritten articles
  When I select "25+" from the Frequency dropdown
  Then I see 1 unwritten article
  When I select "All" from the Root dropdown
  Then I see 17 articles

Scenario: Selecting Unwritten Articles
  When I am logged in as an editor
  And I open the user page
  And I click "Assign Unwritten Articles" under "Editor Tools"
  Then I see the Assign Unwritten Articles page
  And I see 20 unwritten articles
  And no articles are selected
  And the select all checkbox is not selected
  When I click the select checkbox for the first item
  Then the first article is selected
  And the select all checkbox is not selected
  When I click the select checkbox for all unselected articles
  Then all articles are selected
  And the select all checkbox is selected
  When I click the select checkbox for the first item
  Then the first article is not selected
  And the select all checkbox is not selected
  When I click the select all checkbox
  Then all articles are selected
  And the select all checkbox is selected
  When I click the select all checkbox
  Then no articles are selected
  And the select all checkbox is not selected
  When I click the select all checkbox
  Then all articles are selected
  And the select all checkbox is selected
  When I click the "Next" button
  Then no articles are selected
  And the select all checkbox is not selected

Scenario: Viewing Unwritten Articles
  When I am logged in as an editor
  And I open the user page
  And I click "Assign Unwritten Articles" under "Editor Tools"
  Then I see the Assign Unwritten Articles page
  And the first article is for the lemma "ἀβασάνιστος"
  And the the first article has semantic group "Inquiring Knowing and Understanding"
  And the first article has the root βάσανος
  And the first article has 1 occurrence
  When I click the "View" button for the first article
  Then I see the entry page for ἀβασάνιστος

Scenario: Assign and Unassign Articles to a User
  When I am logged in as an editor
  And I open the user page
  Then I see a number of unassigned articles next to "Assign Unwritten Articles."
  When I click "Assign Unwritten Articles" under "Editor Tools"
  Then I see the Assign Unwritten Articles page
  And I see 20 unwritten articles
  And the first article is for the lemma "ἀβασάνιστος"
  When I click the select all checkbox
  And I select the first contributor from the assign articles dropdown
  And I click the Assign button
  Then I see a popup
  When I click "No"
  Then the popup closes
  And I see 20 unwritten articles
  And all articles are selected
  When I click the Assign button
  Then I see a popup
  When I click "Yes"
  Then I see the Assign Unwritten Articles page
  And I see 20 unwritten articles
  And the first article is for the lemma "ἀγγεῖον"
  When I click "Assigned" under the type of article to view
  Then I see 20 unwritten articles assigned to the first contributor
  And there are 20 total articles to display
  When I click the select all checkbox
  And I click the select checkbox for the first item
  And I click the select checkbox for the second item
  And I click the select checkbox for the third item
  And I click the select checkbox for the fifth item
  And I select "Unassigned" from the assign articles dropdown
  Then I see a popup
  When I click "Yes"
  Then I see the Assign Unwritten Articles page
  Then I see 4 unwritten articles assigned to the first contributor
  When I click "Unassigned" under the type of article to view
  Then I see 20 unwritten articles
  And the first article is for the lemma "Ἀβδηρίτης"
  When I click the select checkbox for the first item
  And I select the second contributor from the assign articles dropdown
  And I click the Assign button
  Then I see a popup
  When I click "Yes"
  Then I see 20 unwritten articles
  And the first article is for the lemma "ἀβουλία"
  When I click "Assigned" under the type of article to view
  Then I see 5 unwritten articles


# Should be the same contributor chosen above
Scenario: Article Builder Guidelines
  When I am logged in as the first contributor
  And I open the user page
  And I click "Article Guidelines" under "Contributor Tools"
  Then a word document with the article guidelines is downloaded

Scenario: Article Builder Searching
  When I am logged in as the first contributor
  And I open the user page
  And I click "Article Builder" under "Contributor Tools"
  Then I see a page to search for lemmata
  When I search for "Ἀβδηρίτης"
  Then I see the Article Builder page for Ἀβδηρίτης
  Then I see a warning that says the article has been assigned to another user


Scenario: Article Builder Layout
  When I am logged in as the first contributor
  And I open the user page
  And I click "Article Builder" under "Contributor Tools"
  Then I see a page to search for lemmata
  When I search for "ἐπιπλέω"
  Then I see the Article Builder page for ἐπιπλέω
  And the Style Guide is hidden
  When I click "(show)" for the Style Guide
  Then the Style Guide is visible
  When I click "(hide)" for the Style Guide
  And the Style Guide is hidden
  And there are 42 occurrences under Info
  And the occurrences are in increasing order by location
  When I click "By Context" for the occurrence list
  Then the occurrences are grouped by context
  When I click "By Previous Word" for the occurrence list
  Then the occurrences are grouped by the previous word
  When I click "By Next Word" for the occurrence list
  Then the occurrences are grouped by the next word
  When I click "Export CSV"
  Then a CSV downloads containing occurrence info


Scenario: Articles Assigned to Me and Article Builder From Blank
  When I am logged in as the first contributor
  And I open the user page
  Then I see the number 4 next to the "Articles Assigned to You" link
  When I click "Articles Assigned to You" under "Contributor Tools"
  Then I see the page for articles assigned for me
  And there are 4 articles
  And there is no "First" "Prev" "Next" or "Last" link
  When I click the "Article Builder" link for "Ἄβδηρα"
  Then I see the Article Builder page for Ἄβδηρα
  And there are no errors at the top of the page
  When I type 'Ἄβδηρα (2) I. The town Abdera 2.11.1' into the article draft
  Then I see an article draft warning saying Ἄβδηρα does not occur at 2.11.1
  And I see no article draft errors
  And I see a well-formatted preview of the article
  When I type 'Ἄβδηρα (2) I. The town Abdera 2.97.1' into the article draft
  Then I see no article draft warnings
  And I see no article draft errors
  And I see a well-formatted preview of the article
  When I type 'Ἄβδηρα (2) I. The town Abdera Key Passage: 2.97.1 ἀπὸ Ἀβδήρων' into the article draft
  Then I see no article draft warnings
  And I see an article draft error saying the key passage is incorrectly formatted
  When I type 'Ἄβδηρα (2) I. The town Abdera Key Passage: 1.1.1 ἀπὸ Ἀβδήρων "from Abdera"' into the article draft
  Then I see an article draft warning saying Ἄβδηρα does not occur at 1.1.1
  And I see no article draft errors
  And I see a well-formatted preview of the article
  When I type 'Ἄβδηρα (2) I. The town Abdera Key Passage: 2.97.1 ἀπὸ Ἀβδήρων "from Abdera"' into the article draft
  Then I see no article draft warnings
  And I see no article draft errors
  And I see a well-formatted preview of the article
  When I click Download Article
  Then a copy of the article is downloaded
  When I click "Submit Article for Review"
  Then I see a confirmation page
  When I click "Write Another Article"
  Then I see the article builder search page

Scenario: Article Builder existing Draft
  When I am logged in as the first contributor
  And I open the user page
  And I click "Articles Assigned to You" under "Contributor Tools"
  Then I see the page for articles assigned for me
  And there are 4 articles
  And the article Ἄβδηρα says "yes" I have written a draft
  When I click the "Article Builder" link for "Ἄβδηρα"
  Then I see the Article Builder page for Ἄβδηρα
  And I see a warning that says there is a pending draft of this article

Scenario: Article Builder from Old Definition - Old Author
  When I am logged in as the first contributor
  And I open the user page
  And I click "Articles Assigned to You" under "Contributor Tools"
  Then I see the page for articles assigned for me
  And there are 4 articles
  When I click the "Article Builder" link for "ἀβασίλευτος"
  Then I see the Article Builder page for ἀβασίλευτος
  And I see a previous article written by Betant
  When I type 'ἀβασίλευτος I. sine rege vivens, 2.80.5, (quote) 2.80.5' into the article draft
  Then I see no article draft warnings
  And I see no article draft errors
  And I see a well-formatted preview of the article
  When I click "Submit Article for Review"
  Then I see a popup asking about the article author
  When I click "Keep Elie Ami Betant as the author of this article"
  Then I see a confirmation page
  When I click "User Dashboard"
  Then I see the user page

Scenario: Article Builder from Old Definition - New Author
  When I am logged in as the first contributor
  And I open the user page
  And I click "Articles Assigned to You" under "Contributor Tools"
  Then I see the page for articles assigned for me
  And there are 4 articles
  When I click the "Article Builder" link for "ἀβασάνιστος"
  Then I see the Article Builder page for ἀβασάνιστος
  And I see a previous article written by Betant
  When I type 'ἀβασάνιστος (1) I. Not examined by torture 1.20.1' into the article draft
  Then I see no article draft warnings
  And I see no article draft errors
  And I see a well-formatted preview of the article
  When I click "Submit Article for Review"
  Then I see a popup asking about the article author
  When I click "Make me the author of this new article"
  Then I see a confirmation page

Scenario: Article Builder with Custom Author
  When I am logged in as the first contributor
  And I open the user page
  And I click "Articles Assigned to You" under "Contributor Tools"
  Then I see the page for articles assigned for me
  And there are 4 articles
  When I click the "Article Builder" link for "ἀβλαβής"
  Then I see the Article Builder page for ἀβλαβής
  And I see a previous article written by Betant
  And the custom author field is hidden
  When I click "+ Add custom author"
  Then the custom author field is visible
  When I click "- Hide custom author"
  Then the custom author field is hidden
  When I type 'ἀβλαβής (3) I. Without harm' into the article draft
  And I click "+ Add custom author"
  And I enter "Gus Tomau Thor" as the custom author
  And I click "Submit Article for Review"
  Then I see a confirmation page

Scenario: View Completed Article Drafts Before Resolution and Edit article Draft
  When I am logged in as the first contributor
  And I open the user page
  And I click "Your Completed Article Drafts" under "Contributor Tools"
  Then I see the page for my article drafts
  And there are three article drafts
  And the article drafts all have status "Awaiting Approval"
  When I click "View" for the article "Ἄβδηρα"
  Then I see a page for the Article Draft for Ἄβδηρα
  And I see a preview of the article draft
  And I do not see a button to accept the article
  And I do not see a button to reject the article
  And I see a button to edit the article
  And I see a button to return to my articles
  And I do not see a button to return to drafts waiting approval
  When I click "Edit Article"
  Then I see an input and preview for the article draft
  When I type 'Ἄβδηρα (2) I. The town of Abdera Key Passage: 2.97.1 ἀπὸ Ἀβδήρων "from Abdera"' into the article draft
  Then I see no article draft warnings
  And I see no article draft errors
  And I see a well-formatted preview of the article
  When I click "Make Changes to Article"
  Then I see the draft page for a new draft
  When I click the back button in my browser
  Then I see the draft page for the old draft
  And I see a message telling me the article was edited with a link to the edited version.
  When I click the "Back to my articles" button
  Then I see the page for my article drafts
  And there are four article drafts
  And the fourth article draft has status "Edited" with a blue coloring

Scenario: View Completed Article Drafts and Reject Drafts
  When I am logged in as an editor
  And I open the user page
  And I click "Review Article Drafts" under "Editor Tools"
  Then I see the page for submitted article drafts
  And there are four article drafts
  When I click "View" for the article with lemma ἀβασίλευτος
  Then I see a page for the Article Draft for ἀβασίλευτος
  And I see a preview of the article draft
  And I see a button to accept the article
  And I see a button to reject the article
  And I see a button to edit the article
  And I see a button to return to my articles
  And I see a button to return to drafts waiting approval
  When I click "Reject"
  Then a confirmation popup appears
  When I click "Cancel"
  Then the popup closes
  And the article is still a draft
  When I click "Reject"
  Then a confirmation popup appears
  When I click "Reject" in the popup
  Then a popup appears
  And the popup says to let the first contributor know what they need to do to improve
  When I click "Okay" in the popup
  Then the popup closes
  And a rejection message appears at the top of the article
  And I do not see a button to accept the article
  And I do not see a button to reject the article
  And I do not see a button to edit the article
  And I see a button to return to my articles
  And I see a button to return to drafts waiting approval
  When I click "Back to submitted articles"
  Then I see the page for submitted article drafts
  And there are three article drafts
  When I click "View" for the article with lemma ἀβασάνιστος
  Then I see a page for the Article Draft for ἀβασάνιστος
  When I click "Reject"
  Then a confirmation popup appears
  When I click "Reject" in the popup
  Then a popup appears
  And the popup says to let the first contributor know what they need to do to improve
  When I click "Okay" in the popup
  Then the popup closes
  When I open the search page
  And I search for "ἀβασίλευτος"
  Then I see the entry page for ἀβασίλευτος
  When I click "more" in the middle section
  Then I see a full definition in the middle section
  And the full definition has not changed and includes "(quote)" at the end

Scenario: View Completed Article Drafts and Accept Draft
  When I am logged in as an editor
  And I open the user page
  And I click "Review Article Drafts" under "Editor Tools"
  Then I see the page for submitted article drafts
  And there are two article drafts
  When I click "View" for the article with lemma Ἄβδηρα
  Then I see a page for the Article Draft for Ἄβδηρα
  And I see a preview of the article draft
  And I see a button to accept the article
  And I see a button to reject the article
  And I see a button to edit the article
  And I see a button to return to my articles
  And I see a button to return to drafts waiting approval
  When I click "Accept"
  Then a confirmation popup appears
  When I click "Cancel"
  Then the popup closes
  And the article is still a draft
  When I click "Accept"
  Then a confirmation popup appears
  When I click "Accept" in the popup
  Then the popup closes
  And an accepted message appears at the top of the article
  And I do not see a button to accept the article
  And I do not see a button to reject the article
  And I do not see a button to edit the article
  And I see a button to return to my articles
  And I see a button to return to drafts waiting approval
  When I click "update which meanings the texts links to" in the confirmation message
  Then I view the update meanings link for Ἄβδηρα
  When I open the search page
  And I search for "Ἄβδηρα"
  Then I see the entry page for Ἄβδηρα
  When I click "more" in the middle section
  Then I see a full definition in the middle section
  And I see the full definition has the first contributor as author
  And the full definition matches the input I included earlier, with a single key passage

Scenario: View Completed Article Drafts and Accept Draft with Custom Author
  When I am logged in as an editor
  And I open the user page
  And I click "Review Article Drafts" under "Editor Tools"
  Then I see the page for submitted article drafts
  And there is one article draft
  When I click "View" for the article with lemma ἀβλαβής
  Then I see a page for the Article Draft for ἀβλαβής
  And I see a preview of the article draft
  And I see a button to accept the article
  And I see a button to reject the article
  And I see a button to edit the article
  And I see a button to return to my articles
  And I see a button to return to drafts waiting approval
  When I click "Accept"
  Then a confirmation popup appears
  When I click "Accept" in the popup
  Then the popup closes
  And an accepted message appears at the top of the article
  And I do not see a button to accept the article
  And I do not see a button to reject the article
  And I do not see a button to edit the article
  And I see a button to return to my articles
  And I see a button to return to drafts waiting approval
  When I open the search page
  And I search for "ἀβλαβής"
  Then I see the entry page for ἀβλαβής
  When I click "more" in the middle section
  Then I see a full definition in the middle section
  And I see the full definition has "Gus Tomau Thor" as author
  And the full definition matches the input I included earlier "ἀβλαβής (3) I. Without harm"

Scenario: View Completed Article Drafts
  When I am logged in as the first contributor
  And I open the user page
  And I click "Your Completed Article Drafts" under "Contributor Tools"
  Then I see the page for my article drafts
  And there are four article drafts
  And the first article draft has status "Accepted" with a green coloring
  And the second article draft has status "Accepted" with a green coloring
  And the third article draft has status "Rejected" with a red coloring
  And the fourth article draft has status "Edited" with a blue coloring

# Todo: More specific
Scenario: Articles Change Log
  When I am logged in as an administrator
  And I open the user page
  And I click "Change Log" under "Admin Tools"
  Then I see a log of changes matching what was done for these tests
