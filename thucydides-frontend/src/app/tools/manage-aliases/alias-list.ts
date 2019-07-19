// Classes for aliases and alias lists

import { PaginationList } from '../mini-pagination/pagination-list';

export class Alias {
  aliasid: number;
  alias: string;
  lemma: string;
  error: boolean;

  // True if this is an error
  isError(): boolean {
    return this.error;
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    return this.alias;
  }


  // get link for editing this alias
  getEditLink(): string {
    return "/tools/editAlias/" + this.alias;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class AliasError extends Alias {
  constructor() {
    super({});
    this.error = true;
    this.alias = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export class AliasList extends PaginationList<Alias> {
  // Information for displaying a single page of these
  perPage: number = ALIASES_PER_PAGE;
  itemName: string = "Aliases";

  constructor(json: any) {
    super(json);

    // Each individual alias must be initialized as an object
    if ("list" in json) {
      let l = [];
      for (let i = 0; i < json["list"].length; i++) {
        l.push(new Alias(json["list"][i]));
      }
      this.list = l;
    }
  }
}

export class AliasListError extends AliasList {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export const ALIASES_PER_PAGE = 20;
