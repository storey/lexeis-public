# stores functions for taking complicated greek unicode and simplifying it in
# various ways.
import unicodedata
import re

betacodeCharMap = {
    "COMBINING DIAERESIS": "+",
    "COMBINING GREEK YPOGEGRAMMENI": "|",
    "COMBINING COMMA ABOVE": ")",
    "COMBINING REVERSED COMMA ABOVE": "(",
    "COMBINING ACUTE ACCENT": "/",
    "COMBINING GRAVE ACCENT": "\\",
    "COMBINING GREEK PERISPOMENI": "=",
    "α": "a",
    "β": "b",
    "γ": "g",
    "δ": "d",
    "ε": "e",
    "ζ": "z",
    "η": "h",
    "θ": "q",
    "ι": "i",
    "κ": "k",
    "λ": "l",
    "μ": "m",
    "ν": "n",
    "ξ": "c",
    "ο": "o",
    "π": "p",
    "ρ": "r",
    "σ": "s",
    "ς": "s",
    "τ": "t",
    "υ": "u",
    "φ": "f",
    "χ": "x",
    "ψ": "y",
    "ω": "w",
    "Α": "A",
    "Β": "B",
    "Γ": "G",
    "Δ": "D",
    "Ε": "E",
    "Ζ": "Z",
    "Η": "H",
    "Θ": "Q",
    "Ι": "I",
    "Κ": "K",
    "Λ": "L",
    "Μ": "M",
    "Ν": "N",
    "Ξ": "C",
    "Ο": "O",
    "Π": "P",
    "Ρ": "R",
    "Σ": "S",
    "Τ": "T",
    "Υ": "U",
    "Φ": "F",
    "Χ": "X",
    "Ψ": "Y",
    "Ω": "W"
}


latinCharMap = {
    "COMBINING DIAERESIS": "",
    "COMBINING GREEK YPOGEGRAMMENI": "i",
    "COMBINING COMMA ABOVE": "",
    "COMBINING REVERSED COMMA ABOVE": "h",
    "COMBINING ACUTE ACCENT": "",
    "COMBINING GRAVE ACCENT": "",
    "COMBINING GREEK PERISPOMENI": "",
    "α": "a",
    "β": "b",
    "γ": "g",
    "δ": "d",
    "ε": "e",
    "ζ": "z",
    "η": "e",
    "θ": "th",
    "ι": "i",
    "κ": "k",
    "λ": "l",
    "μ": "m",
    "ν": "n",
    "ξ": "x",
    "ο": "o",
    "π": "p",
    "ρ": "r",
    "σ": "s",
    "ς": "s",
    "τ": "t",
    "υ": "u",
    "φ": "ph",
    "χ": "kh",
    "ψ": "ps",
    "ω": "o",
    "Α": "A",
    "Β": "B",
    "Γ": "G",
    "Δ": "D",
    "Ε": "E",
    "Ζ": "Z",
    "Η": "E",
    "Θ": "TH",
    "Ι": "I",
    "Κ": "K",
    "Λ": "L",
    "Μ": "M",
    "Ν": "N",
    "Ξ": "X",
    "Ο": "O",
    "Π": "P",
    "Ρ": "R",
    "Σ": "S",
    "Τ": "T",
    "Υ": "U",
    "Φ": "PH",
    "Χ": "KH",
    "Ψ": "PS",
    "Ω": "O"
}

# get associated betacode character
def getBetacodeChar(c):
    if unicodedata.combining(c) == 0:
        return betacodeCharMap[c]
    else:
        return betacodeCharMap[unicodedata.name(c)]


def getLatinChar(c):
    if unicodedata.combining(c) == 0:
        return latinCharMap[c]
    else:
        return latinCharMap[unicodedata.name(c)]

def preprocess(lemma):
    return re.sub(r'[,\d\s]', r'', lemma)

# get searchable version of the lemma
def searchVersion(lemma):
    lemma = preprocess(lemma)
    return lemma.lower()

# get unaccented
def unaccented(lemma):
    lemma = preprocess(lemma)
    unacc = "".join([c for c in unicodedata.normalize("NFD", lemma) if unicodedata.combining(c) == 0])
    return unacc.lower()

# get betacode
def betacode(lemma):
    lemma = preprocess(lemma)
    bcode = "".join([getBetacodeChar(c) for c in unicodedata.normalize("NFD", lemma)])
    return bcode.lower()

# get unaccented betacode
def unaccentedBetacode(lemma):
    lemma = preprocess(lemma)
    return betacode(unaccented(lemma))

# get latin approximation
def latinApproximation(lemma):
    lemma = preprocess(lemma)
    base = "".join([getLatinChar(c) for c in unicodedata.normalize("NFD", lemma)])
    new = re.sub(r'([aeiou])h', r'h\1', base)
    return new.lower()

if __name__ == "__main__":
    test = [
        "αβαλ",
        "αβγδεζηθικλμνξοπρσςτυφχψω",
        "ῇβαλ",
        "ἀβάλ",
        "ἄβαλ",
        "Ἄβαλ",
        "ῃβἆλἁὰάϊ",
        "ἱἱἱβαλ",
    ]
    for s in test:
        print(s)
        print(unaccented(s))
        print(betacode(s))
        print(unaccentedBetacode(s))
        print(latinApproximation(s))
        print("---")
