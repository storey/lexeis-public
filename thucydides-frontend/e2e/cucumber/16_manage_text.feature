Feature: I am able to edit information about the text

# These should be done in order; they rely on state from the previous tests
# This is based on the Thucydides in June 2019, some numbers might be slightly different

# Start by verifying old state, then check new state
Scenario: Edit A Token
  When I am logged in as an editor
  And I open the text page
  And I jump to section 1.7.1
  Then I see section 1.7.1
  When I turn on context highlighting
  Then I see the entire section is authorial
  When I click "θαλάσσης"
  Then I see the entry page for θάλασσα
  And lemma meaning V is highlighted
  When I open the user page
  And I click "Manage Text" under "Editor Tools"
  Then I see the Manage Text page
  When I enter section 1.7.1 in the "Choose Section" field
  And I click "Go"
  Then I see the Edit Section page for 1.7.1
  When I click the "Edit" link for Token Τῶν
  Then I see the Edit Word page for τῶν (5045345)
  When I click (Back to Section 1.7.1)
  Then I see the Edit Section page for 1.7.1
  When I click the "Edit" link for Token θαλάσσης
  Then I see the Edit Word page for θαλάσσης (5045392)
  When I enter "θάλ" as the lemma
  And I click "Edit Word"
  An error appears saying 'Lemma "θάλ" does not exist.'
  When I enter "βουλεύω" as the lemma
  And I click "Edit Word"
  An error appears saying 'The definition of "βουλεύω" has no subheading "V".'
  When I enter "II" as the meaning
  And I select "Indirect Speech" as the context
  And I click "Edit Word"
  Then I see the Edit Section page for 1.7.1
  When I open the text page
  And I jump to section 1.7.1
  Then I see section 1.7.1
  Then I see the entire section is authorial except θαλάσσης
  When I click "θαλάσσης"
  Then I see the entry page for βουλεύω
  And lemma meaning II is highlighted
  When I click "more" in the top section
  The Word Frequency is 114 (one more than before)
  The Speech (Indirect) Frequency is 10 (one more than before)

Scenario: Manage Lemma Meanings
  When I am logged in as an editor
  And I open the text page
  And I jump to section 5.6.1
  Then I see section 5.6.1
  When I click "περιέπλευσεν"
  Then I see the entry page for περιπλέω
  And no lemma meaning is highlighted
  When I open the user page
  And I click "Manage Text" under "Editor Tools"
  Then I see the Manage Text page
  When I search for "περιπλέω" under "Manage Lemma Meanings"
  Then I see the page for Managing Lemma Meanings Links
  When I enter '' for the first meaning
  Then the first meaning is ''
  When I enter '3' for the second meaning
  Then an error message appears saying this is not a valid reference
  When I click "Autoload Long Definition Meanings"
  Then the first meaning is 'I'
  And the second meaning is 'I'
  When I enter 'I' for 5.6.1
  And I click "Update Meanings"
  Then I see the Manage Text page
  When I open the text page
  And I jump to section 5.6.1
  Then I see section 5.6.1
  When I click "περιέπλευσεν"
  Then I see the entry page for περιπλέω
  And lemma meaning I is highlighted

Scenario: Recompile Texts
  When I am logged in as an editor
  And I open the user page
  And I click "Manage Text" under "Editor Tools"
  Then I see the Manage Text page
  When I click "recompile them all from here"
  Then I see the page for recompiling texts
  When I click recompile
  Then I see a slowly filling progress bar
  And I see the percentage completed
  When I wait for the progress bar to finish
  Then I see a "Process Complete" success message

# Todo: More specific
Scenario: Articles Change Log
  When I am logged in as an administrator
  And I open the user page
  And I click "Change Log" under "Admin Tools"
  Then I see a log of changes matching what was done for these tests
