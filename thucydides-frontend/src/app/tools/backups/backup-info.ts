// Information for backups

import { PaginationList } from '../mini-pagination/pagination-list';

export class BackupFile {
  filename: string;
  timestamp: string;
  error: boolean;

  // True if this is an error
  isError(): boolean {
    return this.error;
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    return this.filename;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class BackupError extends BackupFile {
  constructor() {
    super({});
    this.error = true;
    this.filename = "There was an error communicating with the server. Please check your internet connection.";
  }
}

// Information for displaying a single page of backups
export class BackupList extends PaginationList<BackupFile> {
  perPage: number = BACKUPS_PER_PAGE;
  itemName: string = "Backups";

  constructor(json: any) {
    super(json);

    // Each backup file needs to be initailized as its own object
    if ("list" in json) {
      let l = [];
      for (let i = 0; i < json["list"].length; i++) {
        l.push(new BackupFile(json["list"][i]));
      }
      this.list = l;
    }
  }
}

export class BackupListError extends BackupList {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export const BACKUPS_PER_PAGE = 20;
