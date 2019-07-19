// articles and list of articles
import { PaginationList } from '../mini-pagination/pagination-list';

export class Article {
  id: number;
  author: string;
  articleModifier: string;
  lemmaid: number;
  lemma: string;
  raw: string;
  longDef: string;
  occurrences: string[][];
  status: number;
  successor: number;

  // True if this is an error
  isError(): boolean {
    return this.id == -1;
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    return this.raw;
  }

  // get link to this article
  getArticleLink(): string {
    return "/tools/articleDraft/" + this.id;
  }


  // Get name for this article's status
  getArticleStatusName() {
    if (this.status == 3) {
      return "Accepted";
    } else if (this.status == 2) {
      return "Rejected";
    } else if (this.status == 1) {
      return "Edited";
    } else {
      return "Awaiting Approval";
    }
  }

  // get the classes for a row with this article
  getRowClasses(): string {
    if (this.status == 3) {
      return "table-success";
    } else if (this.status == 2) {
      return "table-danger";
    } else if (this.status == 1) {
      return "table-info";
    } else {
      return "";
    }
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class ArticleError extends Article {
  constructor() {
    super({});
    this.id = -1;
    this.raw = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export class ArticleList extends PaginationList<Article> {
  // Information for displaying a single page of these
  perPage: number = ARTICLES_PER_PAGE;
  itemName: string = "Articles";

  constructor(json: any) {
    super(json);
    let l = [];
    for (let i = 0; i < json["list"].length; i++) {
      l.push(new Article(json["list"][i]));
    }
    this.list = l;
  }
}

export class ArticleListError extends ArticleList {
  constructor() {
    super({"list": []});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export const ARTICLES_PER_PAGE = 20;
