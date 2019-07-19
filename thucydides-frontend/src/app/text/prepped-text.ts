// A PreppedText contains pre-prepared HTML for a given part of the text.


export class PreppedText {
  sectionCode: string;
  rawHTML;
  note: string;

  // True if this is an error
  isError(): boolean {
    return this.sectionCode == "ERROR";
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    return this.note;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class PreppedTextError extends PreppedText {
  constructor() {
    super({});
    this.sectionCode = "ERROR";
    this.note = "There was an error communicating with the server. Please check your internet connection.";
  }
}
