# Public Repository for Lexeis

This repository contains (most) of the code used in the creation of the lexicon resources at [lexeis.org](lexeis.org).

There are three main pieces for any lexicon, and I include the code for the Thucydides Lexicon here.

First, one uses the scripts in `thucydides-utilities` to compile input resources into the necessary data for the lexicon. This folder contains instructions on how to get the data, run the scripts, and where to put the results.

Second, you compile the frontend part of the program. The source code for this Angular project is available in `thucydides-frontend`, including instructions on where to place the compiled result.

Finally, `lexeis-backend` contains the backend scripts used by the site to load information about individual words, text, etc.
