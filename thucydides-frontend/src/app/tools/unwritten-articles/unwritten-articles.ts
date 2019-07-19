// articles and list of articles
import { PaginationList } from '../mini-pagination/pagination-list';

export class UnwrittenArticle {
  lemmaid: number;
  lemma: string;
  hasOld: boolean;
  freq: number;
  assigned: string;
  checked: boolean;
  semanticGroups: number[];
  root: string[];
  draftSubmitted: boolean;

  // Get link to the article builder
  getArticleBuilderLink(): string {
    return "/tools/articleBuilder/" + this.lemma;
  }

  // get the classes to display this article on a row
  rowClass(): string {
    if (this.checked) {
      return "table-primary";
    } else {
      return "";
    }
  }

  // Get link to the article
  getArticleLink(): string {
    return "/entry/" + this.lemma;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class UnwrittenArticleList extends PaginationList<UnwrittenArticle> {
  // Information for displaying a single page of these
  perPage: number = UNWRITTEN_PER_PAGE;
  itemName: string = "Unwritten Articles";

  constructor(json: any) {
    super(json);

    if ("list" in json) {
      let l = [];
      for (let i = 0; i < json["list"].length; i++) {
        l.push(new UnwrittenArticle(json["list"][i]));
      }
      this.list = l;
    }
  }
}

export class UnwrittenListError extends UnwrittenArticleList {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export const UNWRITTEN_PER_PAGE = 20;


// information for the lists

export class Contributor {
  id: number;
  name: string;
}

export class rootGroup {
  id: number;
  name: string;
}

export class semanticGroup {
  id: number;
  name: string;
}

export class UnwrittenPageInfo {
  isError: boolean;
  message: string;
  contributors: Contributor[];
  rootGroups: rootGroup[];
  semanticGroups: semanticGroup[];

  // True if this is an error
  error(): boolean {
    return this.isError;
  }

  // If this is an error, return the associated error message
  errorMessage(): string {
    return this.message;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class UnwrittenPageInfoError extends UnwrittenPageInfo {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}
