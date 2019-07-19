Feature: I am able to view the text of Thucydides in a variety of ways

Scenario: Visit the text page from main button
  When I open the main page
  And I click the "View Text" button
  Then I see the text page

Scenario: Visit the text page from the nav bar
  When I open the main page
  And I click the "Text" nav entry
  Then I see the text page

Scenario: Cycle between grouping by various categories
  When I open the text page
  Then I see section 1.1.1
  And I see 51 words
  And the previous button is disabled
  And the next button is enabled
  When I click the next button
  Then I see section 1.1.2
  And I see 21 words
  And the previous button is enabled
  And the next button is enabled
  When I click the previous button
  Then I see section 1.1.1
  When I click Group By Chapter
  Then I see chapter 1.1
  And I see 112 words
  And the previous button is disabled
  And the next button is enabled
  When I click the next button
  Then I see chapter 1.2
  And I see 219 words
  And the previous button is enabled
  And the next button is enabled
  When I click the previous button
  Then I see chapter 1.1
  When I click Group By Book
  Then I see book 1
  And I see more than 1000 words
  And the previous button is disabled
  And the next button is enabled
  When I click the next button
  Then I see book 2
  And I see more than 1000 words
  And the previous button is enabled
  And the next button is enabled
  When I click the previous button
  Then I see book 1

Scenario: Jump to categories
  When I open the text page
  Then I see section 1.1.1
  And I see 51 words
  When I jump to section 2.3.1
  Then I see section 2.3.1
  And I see 43 words
  When I try to jump to section 2.3.11
  Then I see an error
  And the jump to button is disabled
  When I jump to chapter 8.109
  Then I see chapter 8.109
  And I see 111 words
  And the previous button is enabled
  And the next button is disabled
  When I jump to book 8
  Then I see book 8
  And I see more than 1000 words
  And the previous button is enabled
  And the next button is disabled


Scenario: Context Highlighting
  When I open the text page
  And I jump to chapter 1.53
  Then I see chapter 1.53
  When I turn context highlighting on
  Then I see the context highlighting legend
  And I see multiple highlighted sections in the text
  When I turn context highlighting off
  Then I do not see the context highlighting legend
  And I see no highlighted sections in the text


Scenario: Browse Sections
  When I open the text page
  Then I see section 1.1.1
  Then I can browse chapters of Book 1
  And I can browse sections of Chapter 1
  When I click Book 1 Chapter 1 Section 3 in Browse Sections
  Then I see section 1.1.3
  When I click Book 1 Chapter 2 in Browse Sections
  Then I see section 1.1.3
  And I can browse chapters of Book 1
  And I can browse sections of Chapter 2
  When I click Book 1 Chapter 2 Section 1 in Browse Sections
  Then I see section 1.2.1
  When I jump to chapter 2.3
  Then I can browse chapters of Book 2
  And I cannot browse any sections
  When I click Book 2 Chapter 1 in Browse Sections
  Then I see section 2.1
  When I click Book 3 in Browse Sections
  Then I see section 2.1
  And I can browse chapters of Book 3
  And I cannot browse any sections
  When I jump to book 4
  Then I see book 4
  And I cannot browse any chapters
  And I cannot browse any sections
  When I click Book 3 in Browse Sections
  Then I see book 3

# Clicking Text => Text Highlighting
Scenario: Navigating between text and entry
  When I open the text page
  Then I see section 1.1.1
  When I click μέγαν
  Then I see the entry page for μέγας
  And I see a full definition in the middle section
  And meaning 'I' is highlighted
  When I click 1.1.2 in the definition
  Then I see section 1.1.2
  And "μεγίστη" is highlighted
  When I click "ἀνθρώπων"
  Then I see the entry page for ἄνθρωπος
  When I click "show" in the bottom section
  And I click 4.33.2 in the occurrence list
  Then I see section 4.33.2
  And "ἄνθρωποι" is highlighted
