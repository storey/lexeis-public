// Stores a small amount of information about an entry, for use in lists and such

export class ShortEntry {
  token: string;
  destination: string; // What entry to go to
  shortDef: string; // also contains error messages when necessary
  isAlias: boolean;

  // True if this is an error
  isError(): boolean {
    return this.token == "ERROR_TOKEN";
  }

  // If this is an error, return the associated error message
  errorMessage(): string {
    return this.shortDef;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class ShortEntryError extends ShortEntry {
  constructor() {
    super({});
    this.token = "ERROR_TOKEN";
    this.shortDef = "There was an error communicating with the server. Please check your internet connection.";
  }
}
