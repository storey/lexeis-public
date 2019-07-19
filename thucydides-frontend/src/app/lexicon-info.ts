// =============================================================================
// =============================================================================
// Custom information for this project
// The idea is that you mostly need to change things in this file only to
// adapt this project for another author. You would also need to make changes
// to src/index.html and angular.json

// Title
export const TITLE = "A Thucydidean Lexicon";
export const AUTHOR_NAME = "Thucydides";
export const AUTHOR_ADJECTIVE = "Thucydidean";
export const AUTHOR_TEXT_TITLE = "Historiae in two volumes";
export const AUTHOR_TEXT_PUBLISHING_INFO = "Oxford, Oxford University Press. 1942.";

// Contact email for the project lead
export const LEAD_EMAIL = "jsr5@cornell.edu";

// Information for the about page

export const ABOUT: string = `Welcome to the Lexeis Thucydidean Lexicon! Thucydides uses his vocabulary in a very specific and precise way,
so an author-specific lexicon for his text is a key resource both for students reading his work for the first
time and established scholars doing a deep dive into the text
This project seeks to update the 19th century concept of the author-specific Lexicon for the 21st century,
using the comprehensive data developed by the Perseus Project at Tufts and augmented for Greek
authors by Helma Dik of the University of Chicago. All citations are linked to the text of Thucydides itself
(and the text is linked back to the lexicon). The lexicon includes short definitions specific to Thucydides,
information on word-frequencies (also frequency by speech (direct and indirect), narrative and authorial
contexts), words of the same stem or prefix, and will include not only new definitions in English but also
relevant bibliography, illustrations and other words of the same semantic category.
Pending completion of new definitions for each word (which we envision occupying several years) we
have incorporated the relevant text of the 1843 lexicon by Bétant with Latin translations divided by
categories, which is still regarded as a highly useful tool.`;
export const HAS_SCREENCAST: boolean = true;
export const SCREENCAST_LINK: string = "../screencasts/screencast.mp4";

// Context Info
export const CONTEXT_TYPES = {
  0: "Narrative",
  1: "Direct Speech",
  2: "Indirect Speech",
  3: "Authorial",
};

export const CONTEXT_ARRAY = ["Narrative", "Direct Speech", "Indirect Speech", "Authorial"];
export const CONTEXT_NAMES_SHORT = ["Narrative", "Speech (Dir)", "Speech (Ind)", "Authorial"];

// export const CONTEXT_TYPES = {
//   0: "Book 1",
//   1: "Book 2",
//   2: "Book 3",
//   3: "Book 4",
//   4: "Book 5",
//   5: "Book 6",
//   6: "Book 7",
//   7: "Book 8",
// };
//
// export const CONTEXT_ARRAY = ["Book 1", "Book 2", "Book 3", "Book 4", "Book 5", "Book 6", "Book 7", "Book 8"];
// export const CONTEXT_NAMES_SHORT = ["Book 1", "Book 2", "Book 3", "Book 4", "Book 5", "Book 6", "Book 7", "Book 8"];


// Information about text
export const TEXT_DIVISIONS = ["Book", "Chapter", "Section"];
export const TEXT_PLACEHOLDERS = ["1", "1.2", "1.2.3"];
export const TEXT_DEFAULT_VALUES = ["-1", "-1", "-1"];
// true if the given text part should be a number
export const TEXT_PART_IS_NUMBER = [true, true, true];
export const NUM_TEXT_DIVISIONS = TEXT_DIVISIONS.length;
export const SMALLEST_TEXT_DIVISION = TEXT_DIVISIONS[NUM_TEXT_DIVISIONS-1];

// Urls for interfacing with the backend. First set are for the lexeis website
// Second set are for website hosted as a subdirectory
// Third set are for local testing with MAMP
// export const BACKEND_URL = "/thucydides/api/";
// export const LEXEIS_URL = "/api/";
// export const BACKEND_URL = "/lexeis/thucydides/api/";
// export const LEXEIS_URL = "/lexeis/api/";
export const BACKEND_URL = "http://localhost:8888/lexeis/thucydides/api/";
export const LEXEIS_URL = "http://localhost:8888/lexeis/api/";

// True if we overwrite the backend's user login information to force a user login.
export const OVERWRITE_USER = true;

// String for the old dictionary
export const OLD_DICTIONARY_REF = "Definition from Betant's Lexicon Thucydideum (1843).";

// Variable name for storing context info in local storage
export const STORAGE_CONTEXT_DISPLAY_NAME = "thuclex-context-display-type";
