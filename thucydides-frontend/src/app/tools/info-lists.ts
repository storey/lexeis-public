// information for editing/adding lemmata, etc

// Classes for storing compound parts, root groups, and semantic groups
export class compoundPart {
  id: number;
  name: string;
}

export class rootGroup {
  id: number;
  name: string;
}

export class semanticGroup {
  id: number;
  name: string;
}

// Options for lemmata
export class LemmaOptionsInfo {
  isError: boolean;
  message: string;
  partsOfSpeech: string[];
  compoundParts: compoundPart[];
  rootGroups: rootGroup[];
  semanticGroups: semanticGroup[];

  // True if this is an error
  error(): boolean {
    return this.isError;
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    return this.message;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class LemmaOptionsError extends LemmaOptionsInfo {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}
