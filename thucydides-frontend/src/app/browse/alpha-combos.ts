// This object contains information about valid two-letter combos that
// begin words.

export class AlphaCombos {
  error: boolean;
  message: string;
  combos: any;

  // True if this is an error
  isError(): boolean {
    return this.error;
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

export class AlphaCombosError extends AlphaCombos {
  constructor() {
    super({});
    this.error = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export class AlphaCombosDefault extends AlphaCombos {
  constructor() {
    super({});
    this.error = false;
    this.message = "";
    this.combos = {};
  }
}
