// List of compounds

import { CompoundInfo } from '../../compound/compound';
import { PaginationList } from '../mini-pagination/pagination-list';

export class CompoundList extends PaginationList<CompoundInfo> {
  // Information for displaying a single page of these
  perPage: number = COMPOUNDS_PER_PAGE;
  itemName: string = "Compounds";

  constructor(json: any) {
    super(json);

    // compounds need to be initalized individually
    if ("list" in json) {
      let l = [];
      for (let i = 0; i < json["list"].length; i++) {
        l.push(new CompoundInfo(json["list"][i]));
      }
      this.list = l;
    }
  }
}

export class CompoundListError extends CompoundList {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export const COMPOUNDS_PER_PAGE = 20;
