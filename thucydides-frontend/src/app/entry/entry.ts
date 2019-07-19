// Frequency information
export class FrequencyHolder {
  all: number;
  contexts: number[];
}

// Information on a single reference to the text
export class RefItem {
  ref: string;
  refLink: string;
  note: string;
}

// Information on a key passage
export class KeyPassageItem {
  ref: string;
  refLink: string;
  greek: string;
  english: string;
}

// This holds a single list item (e.g. I., II.A.) within the long definition.
export class LongDefListItem {
  identifier: string;
  start: string;
  refList: RefItem[];
  keyPassageList: KeyPassageItem[];
}

// A long definition contains info plus potentially a list of recursive sub items.
export class LongDef {
  text: LongDefListItem;
  subList: LongDef[];
}

// The name, index, and display class for a semantic group
export class SemanticPair {
  name: string;
  index: number;
  displayType: string;
}

export class Entry {
  lemmaid: number;
  token: string;
  shortDef: string; // also contains error messages when necessary

  search: string[];

  hasLongDefinition: boolean;
  fullDefinition: string;
  authorName: string;

  partOfSpeech: string;
  semanticGroups: SemanticPair[];
  stemType: string[];
  compoundParts: string[];
  frequency: FrequencyHolder;

  hasKeyPassage: boolean;
  keyPassageLocation: string;
  keyPassageText: string;
  keyPassageTranslation: string;

  hasIllustration: boolean;
  illustrationLink: string;
  illustrationAlt: string;
  illustrationCaption: string;

  bibliographyText: string;
  bibliographyEntries: string[];

  occurrences: string[][];

  status: number;

  // True if this is an error
  isError(): boolean {
    return this.token == "ERROR_TOKEN";
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    return this.shortDef;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }

    if ("bibliographyText" in json) {
      if (this.bibliographyText == "") {
        this.bibliographyEntries = [];
      } else {
        this.bibliographyEntries = this.bibliographyText.split("\n");
      }
    }
  }
}

export class EntryError extends Entry {
  constructor() {
    super({});
    this.token = "ERROR_TOKEN";
    this.shortDef = "There was an error communicating with the server. Please check your internet connection.";
  }
}
