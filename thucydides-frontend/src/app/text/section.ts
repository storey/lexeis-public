// Contains objects for a token and a section

import { CONTEXT_TYPES } from "../lexicon-info";

export class Token {
  tokenIndex: number;
  token: string;
  lemma: string;
  lemmaMeaning: string;
  context: number;
  sectionCode: string;
  sectionLink: string;

  // Convert from context to string
  getEditLink() {
    return "/tools/editToken/" + this.tokenIndex;
  }

  // Get edit link for the associated section
  getSectionEditLink() {
    return "/tools/editText/" + this.sectionCode;
  }

  // Get the context for this token
  getContextString() {
    if (this.context in CONTEXT_TYPES) {
      return CONTEXT_TYPES[this.context];
    } else {
      return "N/A";
    }
  }

  // Return the lemma meaning, or N/A if this has no context
  getMeaningString() {
    if (this.context in CONTEXT_TYPES) {
      return this.lemmaMeaning;
    } else {
      return "N/A";
    }
  }

  // True if this is an error
  isError(): boolean {
    return this.tokenIndex == -1;
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    return this.token;
  }

  // Constructor
  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class TokenError extends Token {
  constructor() {
    super({});
    this.tokenIndex = -1;
    this.token = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export class Section {
  sectionCode: string;
  tokens: Token[];
  note: string;

  // True if this is an error
  isError(): boolean {
    return this.sectionCode == "ERROR";
  }

  // If this is an error, return the associated error message
  errorMessage(): string {
    return this.note;
  }

  constructor(json: any) {
    for(var key in json) {
      if (key == "tokens") {
        let arr = [];
        for (let i = 0; i < json["tokens"].length; i++) {
          let t = json["tokens"][i];
          arr.push(new Token(t))
        }
        this["tokens"] = arr;
      } else {
        this[key] = json[key];
      }
    }
  }
}

export class SectionError extends Section {
  constructor() {
    super({});
    this.sectionCode = "ERROR";
    this.note = "There was an error communicating with the server. Please check your internet connection.";
  }
}
