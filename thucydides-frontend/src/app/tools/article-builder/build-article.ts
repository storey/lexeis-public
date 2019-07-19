// Functions for building articles

// Constants for level items
const ROMANS = ["I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII", "XIII", "XIV", "XV", "XVI", "XVII", "XVIII", "XIX", "XX", "XXI", "XXII", "XXIII", "XXIV", "XXV", "XXVI", "XXVII", "XXVIII", "XXIX", "XXX", "XXXI", "XXXII", "XXXIII", "XXXIV", "XXXV", "XXXVI", "XXXVII", "XXXVIII", "XXXIX", "XL", "XLI", "XLII", "XLIII", "XLIV", "XLV", "XLVI", "XLVII", "XLVIII", "XLIX", "L", "LI", "LII", "LIII", "LIV", "LV", "LVI", "LVII", "LVIII", "LIX", "LX", "LXI", "LXII", "LXIII", "LXIV", "LXV", "LXVI", "LXVII", "LXVIII", "LXIX", "LXX", "LXI", "LXII", "LXIII", "LXIV", "LXV", "LXVI", "LXVII", "LXVIII", "LXIX", "LXX", "LXXXI", "LXXXII", "LXXXIII", "LXXXIV", "LXXXV", "LXXXVI", "LXXXVII", "LXXXVIII", "LXXXIX", "XC"];
const ALPHAS = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE", "AF", "AG", "AH", "AI", "AJ", "AK", "AL", "AM", "AN", "AO", "AP", "AQ", "AR", "AS", "AT", "AU", "AV", "AW", "AX", "AY", "AZ", "BA", "BB", "BC", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BK", "BL", "BM", "BN", "BO", "BP", "BQ", "BR", "BS", "BT", "BU", "BV", "BW", "BX", "BY", "BZ", "CA", "CB", "CC", "CD", "CE", "CF", "CG", "CH", "CI", "CJ", "CK", "CL", "CM", "CN", "CO", "CP", "CQ", "CR", "CS", "CT", "CU", "CV", "CW", "CX", "CY", "CZ", "DA", "DB", "DC", "DD", "DE", "DF", "DG", "DH", "DI", "DJ", "DK", "DL", "DM", "DN", "DO", "DP", "DQ", "DR", "DS", "DT", "DU", "DV", "DW", "DX", "DY", "DZ"];
const NUMERALS = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20"];
const LEVEL_ITEMS = [ROMANS, ALPHAS, NUMERALS];
const MAX_LEVEL = LEVEL_ITEMS.length;

// extract information for a list item that has no sub list
function extractListItem(s: string, identifier: string, meanings) {
  let userRefs = [];
  let warningKPs = [];
  let errorKPs = [];

  s = s.replace(/\n/g, ' ');

  // Create regexes for extracting information
  let referenceRegexStr = "\\d+\\.\\d+\\.\\d+";
  let referenceNoteRegexStr = "(?:[ ;,].*?)?";

  let fullRefRegex = referenceRegexStr + referenceNoteRegexStr;

  let refListRegex = new RegExp("(\\d+\\.\\d+\\.\\d+)", "g");

  let p1Regex = new RegExp("(.*?)((?:(?:" + fullRefRegex + ", )*(?:" + fullRefRegex + "))?)$");
  let keyPassageSplit = /([kK]ey [pP]assage(?:s)?:)/;
  let keyPassageBody = /(?:\s*[^\"]+\"[^\"]+\")+\s*$/;

  // Split the text into parts
  let parts = s.split(keyPassageSplit, 3);
  // console.log(parts);
  let mainText = parts[0];
  let keyPassageText = "";
  if (parts.length == 3) {
    // if there appears to be a key passage, make one
    if (keyPassageBody.exec(parts[2]) !== null) {
      keyPassageText = parts[1].concat(parts[2]);
    } else {
      keyPassageText = null;
    }
  }
  let mainMatch = p1Regex.exec(mainText);

  // let m = fullRegex.exec(s);

  let start: string;
  let refList;
  let keyPassageList;
  if (mainMatch == null) {
    // if there is no match, just return the text
    start = s;
    refList = [];
    keyPassageList = [];
  } else {
    // Otherwise, get the start tex, list of references, and key passage
    start = mainMatch[1]; //m[1];
    let l = mainMatch[2]; //m[2];
    let kp = keyPassageText;//m[3];

    // Get references
    let refs = l.split(refListRegex).slice(1); //refListRegex.split(l)[1:];

    refList = [];
    for (let i = 0; i < refs.length; i += 2) {
        let ref = refs[i];
        meanings[ref] = identifier;
        let refLink = ref.replace(".", "/");
        let refNote = refs[i+1];
        refList.push({
            "ref": ref,
            "refLink": refLink,
            "note": refNote
        });
        userRefs.push(ref);
    }

    // get key passages if they exist
    keyPassageList = [];
    if (kp === null) {
      keyPassageList.push({
        "ref": "0.0.0",
        "refLink": "",
        "greek": "PARSING ERROR",
        "english": null
      });
      errorKPs.push([identifier, parts[2]]);
    }
    else if (kp != "") {
      let keyPassages = kp.split(refListRegex).slice(1);
      if (keyPassages.length == 0) {
        errorKPs.push([identifier, parts[2]]);
        keyPassageList.push({
          "ref": "0.0.0",
          "refLink": "",
          "greek": "PARSING ERROR",
          "english": null
        });
      }
      for (let i = 0; i < keyPassages.length; i += 2) {
        let ref = keyPassages[i].trim();
        let refLink = ref.replace(".", "/");

        userRefs.push(ref);

        let text = keyPassages[i+1];

        let greekText = "";
        let translation = "";

        let tSplit = text.split("\"");
        if (tSplit.length > 1) {
          greekText = tSplit[0].trim();
          translation = tSplit[1].trim();
        }

        if (greekText === "") {
          warningKPs.push([identifier, "Greek"]);
        }
        if (translation == "") {
          warningKPs.push([identifier, "English"]);
        }

        keyPassageList.push({
          "ref": ref,
          "refLink": refLink,
          "greek": greekText,
          "english": translation
        })
      }
    }
  }
  let item = {
      "identifier": identifier,
      "start": start,
      "refList": refList,
      "keyPassageList": keyPassageList
  }
  // Include error information
  let result = {
    "item": item,
    "userRefs": userRefs,
    "warningKPs": warningKPs,
    "errorKPs": errorKPs
  }
  return result;
}

// create a list item that has no sub list
function makeListItem(s: string, identifier: string, meanings) {
  // Extract info
  let listItemPlus = extractListItem(s, identifier, meanings);

  // repackage it in the correct format 
  let res = {
    "list": {
      "text": listItemPlus["item"],
      "subList": [],
    },
    "problems": {
      "userRefs": listItemPlus["userRefs"],
      "warningKPs": listItemPlus["warningKPs"],
      "errorKPs": listItemPlus["errorKPs"]
    }
  }
  return res;
}

// given a string and a level, extract a title and a list
export var extractListAndIssues = function(s, level, identifier, meanings) {
  let userRefs = [];
  let warningKPs = [];
  let errorKPs = [];

  if (level > MAX_LEVEL) {
      return makeListItem(s, identifier, meanings);
  } else {
      let lev = LEVEL_ITEMS[level - 1];
      let listPieces = [];
      let current = s;
      // go through the text looking for the next identifier,
      // e.g. I then II then III, and split the text into pieces based on this.
      for (let i = 0; i < lev.length; i++) {
          let l = lev[i];
          let fullSplitter = l + ". ";
          // split only on first instance
          let fullSplit = current.split(fullSplitter);
          // if there are multiple options, we have to re-collapse the end
          if (fullSplit.length > 1) {
            fullSplit = [fullSplit[0], fullSplit.slice(1).join(fullSplitter)];
          }

          // if no list exists, just append this as is
          if ((i == 0) && (fullSplit.length == 1)) {
              listPieces.push(current);
              break;
          } else {
              let piece = fullSplit[0].trim();
              if (i != 0) {
                  let preIdentifier = "";
                  if (identifier != "") {
                      preIdentifier = identifier + ".";
                  }
                  let nextIdentifier = preIdentifier + lev[i-1];
                  let pieceAndProblems = extractListAndIssues(piece, level+1, nextIdentifier, meanings);
                  piece = pieceAndProblems["list"];
                  userRefs = userRefs.concat(pieceAndProblems["problems"]["userRefs"]);
                  warningKPs = warningKPs.concat(pieceAndProblems["problems"]["warningKPs"]);
                  errorKPs = errorKPs.concat(pieceAndProblems["problems"]["errorKPs"]);
              }
              listPieces.push(piece);
              // if we weren't done, get the next piece we look at
              if (!(fullSplit.length == 1)) {
                  current = fullSplit[1].trim()
              } else {
                  break;
              }
          }
      }
      let listItemPlus = extractListItem(listPieces[0], identifier, meanings);
      userRefs = userRefs.concat(listItemPlus["userRefs"]);
      warningKPs = warningKPs.concat(listItemPlus["warningKPs"]);
      errorKPs = errorKPs.concat(listItemPlus["errorKPs"]);
      let res = {
        "list": {
          "text": listItemPlus["item"],
          "subList": listPieces.slice(1),
        },
        "problems": {
          "userRefs": userRefs,
          "warningKPs": warningKPs,
          "errorKPs": errorKPs
        }
      }

      return res;
  }
}
