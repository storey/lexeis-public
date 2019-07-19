# Lexeis Thucydides Lexicon

This repository contains the code for the front end of the the Thucydides Lexicon for
[lexeis.org](https://lexeis.org).


The vast majority of files are in `src/app`, divided into a variety of components.

There are also few feature files in `e2e/cucumber` for manual testing.


To build the production version:
1. Change the URL in `src/index.html` (comment out line 8, comment in line 6)
2. In `src/app/lexicon-info.ts`, set the proper backend URLs to be the `/api/` and `/api/thucydides/` ones, and set OVERWRITE_USER to false
3. Run `ng build --prod --aot`
4. The resulting files will be in `dist/`
5. Copy these files to the backend's `thucydides` folder.
