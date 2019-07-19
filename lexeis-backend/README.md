# Lexeis Backend

This folder contains some of the code for the Lexeis website; code for the top level site's pages and a variety of backend information with login data are omitted.

`api/` contains a few top level database and login utilities.

 `thucydides/` is where you will include the code for the production version of the frontend. It also contains the `api` folder which is used by the frontend to get information of various lemmata, compounds, text sections, etc.

 To set things up the first time, add lexicon_database.db to the `thucydides/api` folder, then run `convertToMySQL.php` to create the databases and prepare the preprocessed versions of the text. You'll probably want to delete the file from the server after that so random people can't reset the database to scratch. For more info on how the backend files work, look at `thucydides/api/server_only/README.md`.
