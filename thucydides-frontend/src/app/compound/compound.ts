// Store information about a compound

import { ShortEntry } from '../entry/short-entry';

export class CompoundInfo {
  index: number;
  name: string;
  description: string;
  matchingLemmas: ShortEntry[];
  associatedLemma: string;

  // True if this is an error
  isError(): boolean {
    return this.index == -1;
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    return this.description;
  }

  // Get the link for editing this group
  getEditLink(): string {
    return "/tools/editCompoundPart/" + this.name;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class CompoundInfoError extends CompoundInfo {
  constructor() {
    super({});
    this.index = -1;
    this.description = "There was an error communicating with the server. Please check your internet connection.";
  }
}
