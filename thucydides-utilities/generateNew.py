# Generate empty versions of the files needed

from openpyxl import Workbook
import sqlite3

import utils
import simplifyUnicode
from parameters import DB_LOCATION

getAlpha = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"]

# Create an excel file given a filename and array of rows
def createXLS(filename, rows):
    wb = Workbook()
    sheet = wb.create_sheet("Worksheet")
    for i in range(len(rows)):
        row = rows[i]
        for j in range(len(row)):
            cell = row[j]

            spec = getAlpha[j] + str(i+1)
            sheet[spec] = cell

    wb.remove(wb["Sheet"])
    wb.save(filename)

# Check for db
if (not(utils.fileExists(DB_LOCATION))):
    raise Exception("Please add a database at '" + DB_LOCATION + "'")

# Create input folder and subfolders
utils.check_and_create_path("input/articles")
utils.check_and_create_path("input/illustrations")

utils.safeWrite("input/articles/README.md", "Place articles here. Articles should be included in a text file and the filename should be [lemma].txt, where [lemma] is the lemma the article is about. For example, the article for ἄβουλος should be in ἄβουλος.txt")
utils.safeWrite("input/illustrations/README.md", "Place illustrations here. Illustrations should be a .jpg, .gif, or .png with the name of the lemma they are an illustraiton for. For example, the image for ἄβουλος should be in ἄβουλος.png (or ἄβουλος.gif or ἄβουλος.jpg)")

# Create lemmata xlsx
lemma_info = []
lemma_info.append(["Matched", "Lemma", "Short Definition", "Compounds", "Roots", "Sphere", "Part of Communication", "Frequency", "Illustration Caption", "Bibliography", "Notes"])

print("Getting Tokens...")
conn = sqlite3.connect(DB_LOCATION)
c = conn.cursor()
tokens = c.execute('SELECT tokenid FROM tokens')
tokenids = []
for token in tokens:
    tokenids.append(token[0])

print("Getting Lemmas...")
lemmata = {}
for i in range(len(tokenids)):
    tokenid = tokenids[i]
    # select parse that was chosen by an authority, if no
    # authority, choose the one with the highest probability
    bestProb = 0
    lemmaIndex = None
    rows = c.execute('SELECT lex, authority, prob FROM parses WHERE tokenid=%d' % tokenid)
    for row in rows:
        auth = row[1]
        prob = row[2]
        if not(auth == None):
            lemIndex = row[0]
            bestProb = prob
            break
        if prob > bestProb:
            lemIndex = row[0]
            bestProb = prob

    lemma = ""
    if not(lemIndex == None):
        count = 0
        for row2 in c.execute('SELECT lemma FROM Lexicon WHERE lexid=%d' % lemIndex):
            count += 1
            lemma = row2[0]
        if (count == 0):
            print("Lemma index does not exist:" + lemIndex)

    if not(lemma == ""):
        if lemma in lemmata:
            lemmata[lemma] = lemmata[lemma] + 1
        else:
            lemmata[lemma] = 1

print("Sorting Lemmata...")
sortable = []
for key in lemmata:
    sortable.append([key, simplifyUnicode.unaccented(key).lower()])

print("Creating Lemmata Excel File...")
sortedLemmata = sorted(sortable, key=lambda x: x[1])
for i, duo in enumerate(sortedLemmata):
    lemma = duo[0]
    freq = lemmata[lemma]
    lemma_info.append([i, lemma, "", "", "", "", "", freq, "", "", ""])


createXLS("input/lemmata.xlsx", lemma_info)



print("Creating Other Excel Files...")
# Create context xlsx
#TODO: change this for whatever text type ends up being depending on lexicon; these are values for Thucydides.
createXLS("input/contexts.xlsx", [["Book", "Chapter Start", "Section Start", "First Word", "Chapter End", "Section End", "Last Word", "Context Type", "Description", "Notes"]])

# Create other xlsx files
createXLS("input/aliases.xlsx", [["Alias", "Lemma"]])
createXLS("input/compounds.xlsx", [["Compound", "Description"]])
createXLS("input/roots.xlsx", [["Root", "Description"]])
createXLS("input/semanticGroups.xlsx", [["Semantic Group", "Description", "Label Type"]])
