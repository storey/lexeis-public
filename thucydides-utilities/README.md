# Thucydides Dictionary Utilities

Utility scripts for the Thucydides Dictionary Project. It should be used in conjunction with the Thucydides frontend and backend framework.

This takes some key input, in particular a .xml file with the text for an author, a .db file with information on the author's word usage (Both provided by Perseus Chicago), an XML of the previous author's dictionary plus some custom information in Excel files and written articles that you write.


1. Run `generateNew.py` to create a new set of Excel files for creating the dictionary. Once you've generated these files, add the XML file with the text and the database of information to the `input`, plus any illustrations and articles you want.

2. Run `parseBetant.py` to generate articles from the Betant text.

3. Run `parseExcel.py` to generate the necessary lexicon data from input containing information about the text and lexicon of Thucydides in the input folder. It generates a variety of resources, of which you only need three.
- The illustrations folder with illustrations should be put in `src/assets` on the frontend.
- The list of sections in `sections_list.ts` should be put in `src/app/text/` on the frontend.
- An SQLite database of lexicon information in `lexicon_database.db` should be put in `thucydides/api` on the backend.
