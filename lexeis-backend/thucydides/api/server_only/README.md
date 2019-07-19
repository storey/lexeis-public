# README for Thucydides Backend.

Sorry this is in a slightly weird place, but I didn't want it to be publicly available.

This contains the backend php files for supporting the Thucydidean lexicon.

Note that before this can work you need to set up the login credentials in
/api/master.php and /api/thuclex_config.cnf, as well as creating the appropriate
database and user with permissions on the public Reclaim server (as well as locally).

## General Structures

The broad structure for most files is :
1. a few imports at the top (utilities for database interaction
and lexicon utilities).
2. Write some headers
3. Decline to take any action if the request is of type OPTIONS
4. Specify what the error message would look like
5. Check user permissions with the accessGuard function.
6. Open a connection to the database
7. Grab elements out of the POST data using get_data
8. Do whatever you actually want to do, gather information, make changes, etc
9. Close the database connection and echo results.

## Files

Files are divided into a few categories:

### Adding (`add*.php`)
These files add new information to the database.

### Deleting (`delete*.php`)
These files allow deletion of things in the database

### Editing (`edit*.php`)
These files are for editing existing entries in the database

### Getting (`get*.php`)
These files return JSON objects with information about a lemma, piece of text, etc.

### Extra Article Handling
- `assignUnwrittenArticles.php` allows an editor to assign articles for a contributor to write.
- `resolveArticleDraft.php` allows an editor to accept or reject a draft.

### Issue Reporting
- `reportIssue.php` allows users to report an issue.
- `resolveIssue.php` allows an editor to resolve an issue.

### Undo Changes
- `undoChange.php` allows users to undo specific changes.

### Prepared Texts
- `compilePrepTexts.php` contains code for extracting text information from the
database and preprocessing it for quick loading to the frontend.
- `recompilePrepTexts.php` is the file that users interface with when they recompile
all of the texts. It sends multiple status updates.

### Saving Information
- `createNewBackup.php` creates a new backup.
- `restoreBackup.php` restores an old backup.
- `createLexiconExport.php` downloads all lexicon information in more human-readable format.
- `occurrenceCSV.php` downloads information on occurrences of a single lemma.

### Important Files
- `convertToMySQL.php` this file takes the compiled .db file and transfers the
information to mysql, as well as doing some initialization work. *YOU SHOULD NOT
LEAVE THIS UP ON THE SERVER.* **Upload it when you need it and then delete it.**
No reason to let anyone with an internet connection reset the lexicon.
- `lexiconUtils.php` this file contains many useful utility functions.
