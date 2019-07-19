# This file holds functions to take a plaintext version of the text definition
# and convert it to a structured object
import re
import json

ROMANS = ["I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII", "XIII", "XIV", "XV", "XVI", "XVII", "XVIII", "XIX", "XX", "XXI", "XXII", "XXIII", "XXIV", "XXV", "XXVI", "XXVII", "XXVIII", "XXIX", "XXX", "XXXI", "XXXII", "XXXIII", "XXXIV", "XXXV", "XXXVI", "XXXVII", "XXXVIII", "XXXIX", "XL", "XLI", "XLII", "XLIII", "XLIV", "XLV", "XLVI", "XLVII", "XLVIII", "XLIX", "L", "LI", "LII", "LIII", "LIV", "LV", "LVI", "LVII", "LVIII", "LIX", "LX", "LXI", "LXII", "LXIII", "LXIV", "LXV", "LXVI", "LXVII", "LXVIII", "LXIX", "LXX", "LXI", "LXII", "LXIII", "LXIV", "LXV", "LXVI", "LXVII", "LXVIII", "LXIX", "LXX", "LXXXI", "LXXXII", "LXXXIII", "LXXXIV", "LXXXV", "LXXXVI", "LXXXVII", "LXXXVIII", "LXXXIX", "XC"]
ALPHAS = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE", "AF", "AG", "AH", "AI", "AJ", "AK", "AL", "AM", "AN", "AO", "AP", "AQ", "AR", "AS", "AT", "AU", "AV", "AW", "AX", "AY", "AZ", "BA", "BB", "BC", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BK", "BL", "BM", "BN", "BO", "BP", "BQ", "BR", "BS", "BT", "BU", "BV", "BW", "BX", "BY", "BZ", "CA", "CB", "CC", "CD", "CE", "CF", "CG", "CH", "CI", "CJ", "CK", "CL", "CM", "CN", "CO", "CP", "CQ", "CR", "CS", "CT", "CU", "CV", "CW", "CX", "CY", "CZ", "DA", "DB", "DC", "DD", "DE", "DF", "DG", "DH", "DI", "DJ", "DK", "DL", "DM", "DN", "DO", "DP", "DQ", "DR", "DS", "DT", "DU", "DV", "DW", "DX", "DY", "DZ"]
NUMERALS = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20"]
LEVEL_ITEMS = [ROMANS, ALPHAS, NUMERALS]

MAX_LEVEL = len(LEVEL_ITEMS)



# create a list item that has no sub list
def extractListItem(s, identifier, meanings):
    s = re.sub(r'\n', ' ', s)


    referenceRegexStr = "\d+\.\d+\.\d+"
    referenceNoteRegexStr = "(?:[ ;,].*?)?"

    keyPassageRegex = "(?:[kK]ey [pP]assage(?:s)?:(?:\s*[^\"]+\"[^\"]+\")+\s*)?";

    fullRefRegex = referenceRegexStr + referenceNoteRegexStr

    fullRegex = re.compile("(.*?)((?:%s, )*(?:%s))(%s)$" % (fullRefRegex, fullRefRegex, keyPassageRegex))
    refListRegex = re.compile("(\d+\.\d+\.\d+)")

    m = fullRegex.match(s)

    if (m == None):
        start = s
        refList = []
        keyPassageList = []
    else:
        start = m.group(1)
        l = m.group(2)
        kp = m.group(3)

        refs = refListRegex.split(l)[1:]

        refList = []
        for i in range(0, len(refs), 2):
            ref = refs[i]
            meanings[ref] = identifier
            refLink = ref.replace(".", "/")
            refNote = refs[i+1]
            refList.append({
                "ref": ref,
                "refLink": refLink,
                "note": refNote
            })

        # get key passages if they exist
        keyPassageList = []
        if (kp != ""):
            keyPassages = refListRegex.split(kp)[1:]
            for i in range(0, len(keyPassages), 2):
                ref = keyPassages[i].strip()
                refLink = ref.replace(".", "/")

                meanings[ref] = identifier

                text = keyPassages[i+1]
                tSplit = text.split("\"")
                greekText = tSplit[0].strip()
                translation = tSplit[1].strip()

                keyPassageList.append({
                    "ref": ref,
                    "refLink": refLink,
                    "greek": greekText,
                    "english": translation
                })

    item = {
        "identifier": identifier,
        "start": start,
        "refList": refList,
        "keyPassageList": keyPassageList
    }
    return item

# create a list item that has no sub list
def makeListItem(s, identifier, meanings):
    res = {
        "text": extractListItem(s, identifier, meanings),
        "subList": []
    }
    return res

# given a string and a level, extract a title and a list
def extractList(s, level, identifier, meanings):
    if (level > MAX_LEVEL):
        return makeListItem(s, identifier, meanings)
    else:
        lev = LEVEL_ITEMS[level - 1]
        listPieces = []
        current = s
        # go through the text looking for the next identifier,
        # e.g. I then II then III, and split the text into pieces based on this.
        for i in range(len(lev)):
            l = lev[i]
            fullSplitter = l + ". "
            fullSplit = current.split(fullSplitter, maxsplit=1)

            # if no list exists, just append this as is
            if ((i == 0) and (len(fullSplit) == 1)):
                #print("Could not find %s -- start" % fullSplitter)
                listPieces.append(current)
                break
            else:
                piece = fullSplit[0].strip()
                if not(i == 0):
                    preIdentifier = ""
                    if not(identifier == ""):
                        preIdentifier = identifier + "."
                    nextIdentifier = preIdentifier + lev[i-1]
                    piece = extractList(piece, level+1, nextIdentifier, meanings)
                listPieces.append(piece)
                # if we weren't done, get the next piece we look at
                if not(len(fullSplit) == 1):
                    current = fullSplit[1].strip()
                else:
                    break

        res = {
            "text": extractListItem(listPieces[0], identifier, meanings),
            "subList": listPieces[1:]
        }

        return res




# given a raw long definition description, convert it into an object that
# holds the structure more specifically.
def createLongDefObject(raw):
    meanings = {}
    defObj = extractList(raw, 1, "", meanings)
    return defObj, meanings


# given a raw long definition description, convert it into an object that
# holds the structure more specifically.
def createLongDefObjects(raw):
    meanings = {}
    options = raw.split("======---")

    # If no options, just parse and return it
    if (len(options) == 1):
        obj, meanings = createLongDefObject(raw);
        return [obj], meanings

    # Otherwise parts individual pieces
    longDef = []
    for i in range(1, len(options)):
        splt = options[i].split("---======")
        definition, m = createLongDefObject(splt[1])
        if (i == 1):
            meanings = m
        definition["defType"] = splt[0]
        longDef.append(definition)

    return longDef, meanings


# given a lemma, get a list of the
def getBadSections(lemma, allSections):
    rawDef = lemma.longDefinitionRaw
    bads = []
    for m in re.finditer(r'\d+\.\d+\.\d+', rawDef):
        sec = m.group(0)
        if not(sec in allSections):
            bads.append(sec)
    return bads
