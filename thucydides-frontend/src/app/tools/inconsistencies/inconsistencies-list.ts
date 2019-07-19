// list of inconsistencies
export class InconsistenciesList {
  message: string; // contains error messages when necessary
  isError: boolean;
  incList: string[][];

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

export class InconsistenciesListError extends InconsistenciesList {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}
