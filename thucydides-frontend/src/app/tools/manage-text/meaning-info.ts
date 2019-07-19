// Information on meanings

export class MeaningInfo {
  token: string;
  message: string;

  hasLongDefinition: boolean;
  fullDefinition: string;
  authorName: string;

  occurrences: string[][];

  // True if this is an error
  isError(): boolean {
    return this.token == "ERROR_TOKEN";
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

export class MeaningInfoError extends MeaningInfo {
  constructor() {
    super({});
    this.token = "ERROR_TOKEN";
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}
