// Classes for a list of roots

import { RootGroup } from '../../root-group/root-group';
import { PaginationList } from '../mini-pagination/pagination-list';


export class RootList extends PaginationList<RootGroup> {
  // Information for displaying a single page of these
  perPage: number = ROOTS_PER_PAGE;
  itemName: string = "Roots";

  constructor(json: any) {
    super(json);

    if ("list" in json) {
      let l = [];
      for (let i = 0; i < json["list"].length; i++) {
        l.push(new RootGroup(json["list"][i]));
      }
      this.list = l;
    }
  }
}

export class RootListError extends RootList {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export const ROOTS_PER_PAGE = 20;
