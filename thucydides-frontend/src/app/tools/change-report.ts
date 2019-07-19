// Result of user's attempt to make a change on the server
export class ChangeReport {
  isError: boolean;
  message: string;
  // We want to differentiate between no attempt to submit and submit error.
  isDefault: boolean;

  // True if this was successful
  isSuccess(): boolean {
    return !this.isError && !this.isDefault;
  }

  // True if this is an error
  error(): boolean {
    return this.isError && !this.isDefault;
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    return this.message;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
    this.isDefault = false;
  }
}

export class ChangeReportError extends ChangeReport {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export class ChangeReportDefault extends ChangeReport {
  constructor() {
    super({});
    this.isError = false;
    this.isDefault = true;
    this.message = "";
  }
}


// A message from the server with an update on progress of some long operation
export class ProgressUpdate {
  isError: boolean;
  // We want to differentiate between no attempt to submit and submit error.
  isDefault: boolean;
  message: string;
  // number from 0 - 100
  progress: number;
  // true if this is the last update
  complete: boolean;


  // True if this was successful
  isSuccess(): boolean {
    return !this.isError && !this.isDefault;
  }

  // True if this is an error
  error(): boolean {
    return this.isError && !this.isDefault;
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    return this.message;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
    this.isDefault = false;
  }
}

export class ProgressUpdateError extends ProgressUpdate {
  constructor() {
    super({});
    this.isError = true;
    this.complete = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export class ProgressUpdateDefault extends ProgressUpdate {
  constructor() {
    super({});
    this.isError = false;
    this.complete = true;
    this.isDefault = true;
    this.message = "";
  }
}
