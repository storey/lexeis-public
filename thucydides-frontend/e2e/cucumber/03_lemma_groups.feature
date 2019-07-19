Feature: I am able to view pages for compound, root, and semantic groups

# Compound
Scenario: View Compound Group Page
  When I open the search page
  And I search for "epiplew"
  Then I see the entry page for ἐπιπλέω
  When I click "more" in the top section
  Then I see ἐπί as the compound part
  When I click compound part ἐπί
  Then I see a page for the compound ἐπί
  And I see a list of lemmata with this compound part in alphabetical order
  When I click ἀντεπάγω in the lemma list
  Then I see the entry page for ἀντεπάγω
  When I click "more" in the top section
  Then I see ἀντί and ἐπί as the compound parts
  When I click compound part ἀντί
  Then I see a page for the compound ἀντί
  And I see a list of lemmata with this compound part in alphabetical order
  When I click the associated lemma link
  Then I see the entry page for ἀντί

# Root
Scenario: View Root Group Page
  When I open the search page
  And I search for "strategos"
  Then I see the entry page for στρατηγός
  When I click "more" in the top section
  Then I see στρατός and ἄγω as the roots
  When I click root στρατός
  Then I see a page for the root στρατός
  And I see a list of lemmata with this root in alphabetical order
  When I click συστράτηγος in the lemma list
  Then I see the entry page for συστράτηγος
  When I click "more" in the top section
  Then I see στρατός and ἄγω as the roots
  When I click root ἄγω
  Then I see a page for the root ἄγω
  And I see a list of lemmata with this root in alphabetical order
  When I click the associated lemma link
  Then I see the entry page for ἄγω

# Semantic Group
Scenario: View Semantic Group Page
  When I open the search page
  And I search for "paraplew"
  Then I see the entry page for παραπλέω
  When I click "more" in the middle section
  Then I see a full definition in the middle section
  And I see the semantic groups "Movement (Intransitive)" and "Naval"
  When I click the semantic group "Movement (Intransitive)"
  Then I see a page for the semantic group
  And I see a list of lemmata of this semantic group in alphabetical order
  When I click ἀλάομαι in the lemma list
  Then I see the entry page for ἀλάομαι
