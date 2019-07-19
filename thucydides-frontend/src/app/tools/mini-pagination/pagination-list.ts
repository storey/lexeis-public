// Super class for a list that can work with the mini-pagination setup

export abstract class PaginationList<T> {
  message: string;// contains error messages when necessary
  isError: boolean;
  list: T[];
  size: number;

  // Information for displaying a single page of these
  abstract perPage: number;
  abstract itemName: string;

  // True if this is an error
  error(): boolean {
    if (+this.size === 0) {
      return true;
    }
    return this.isError;
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    if (+this.size === 0) {
      return "There are no " + this.itemName.toLowerCase() + " to view at this time.";
    } else {
      return this.message;
    }
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}
