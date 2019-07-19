import { SemanticGroup } from '../../semantic-group/semantic-group';
import { PaginationList } from '../mini-pagination/pagination-list';

export class SemanticGroupList extends PaginationList<SemanticGroup> {
  // Information for displaying a single page of these
  perPage: number = SEMANTIC_GROUPS_PER_PAGE;
  itemName: string = "Semantic Groups";

  constructor(json: any) {
    super(json);

    if ("list" in json) {
      let l = [];
      for (let i = 0; i < json["list"].length; i++) {
        l.push(new SemanticGroup(json["list"][i]));
      }
      this.list = l;
    }
  }
}

export class SemanticGroupListError extends SemanticGroupList {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}

// export const SEMANTIC_GROUP_LIST_ERROR: SemanticGroupList = {
//   message: "Error Loading List of Semantic Groups",
//   isError: true,
//   list: [],
//   size: 0,
// }


export const SEMANTIC_GROUPS_PER_PAGE = 20;
