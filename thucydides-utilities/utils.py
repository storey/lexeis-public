# -*- coding: utf-8 -*-
# Utility functions

import os
import json
import sys
import errno
import unicodedata

# check if the given file path exists, and if not create it.
# based on Krumelur's answer to
# http://stackoverflow.com/questions/12517451/python-automatically-creating-directories-with-file-output
def check_and_create_path(filename):
    if (not os.path.exists(os.path.dirname(filename))):
        try:
            os.makedirs(os.path.dirname(filename))
        except OSError as exc: # Guard against race condition
            if exc.errno != errno.EEXIST:
                raise

# true if the file exists
def fileExists(filename):
    return os.path.isfile(filename)

# Get normalized list of files in the given directory
def getNormalizedFilesList(dir):
    return list(map(lambda x: unicodedata.normalize("NFD", x), os.listdir(dir)))


def getNormalizedString(fname):
    return unicodedata.normalize("NFD", fname)

# write content to the file at filename. Make the directory path to the given
# file if it does not exist.
def safeWrite(filename, content, dumpJSON=False):
    check_and_create_path(filename)
    out_file = open(filename, "w")
    if dumpJSON:
        content = json.dumps(content)
    out_file.write(content)
    out_file.close()

# get the content from a given file by reading it
# parseJSON is true if we should parse the contents as JSON first
def getContent(inFileName, parseJSON):
    inFile = open(inFileName, 'r')
    inContents = inFile.read()
    inFile.close()
    if parseJSON:
        return json.loads(inContents)
    else:
        return inContents
