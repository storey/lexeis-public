// list of issues
import { PaginationList } from '../mini-pagination/pagination-list';

export class ReportedIssue {
  id: number;
  is_user: boolean;
  email: string;
  tstamp: string;
  location: string;
  comment: string;
  resolved: boolean;
  resolved_user: string;
  resolved_tstamp: string;
  resolved_comment: string;

  // True if this is an error
  isError(): boolean {
    return +this.id === -1;
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    return this.comment;
  }

  // get classes for a row
  getRowClasses(): string {
    if (this.resolved) {
      return "table-success";
    } else {
      return "table-danger";
    }
  }

  // get the router link for the issue list
  getIssueLink(): string {
    return "/tools/issue/" + this.id;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class IssueError extends ReportedIssue {
  constructor() {
    super({});
    this.id = -1;
    this.comment = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export class IssuesList extends PaginationList<ReportedIssue> {
  // Information for displaying a single page of these
  perPage: number = ISSUES_PER_PAGE;
  itemName: string = "Issues";

  constructor(json: any) {
    super(json);

    // Each issue must be an object
    if ("list" in json) {
      let l = [];
      for (let i = 0; i < json["list"].length; i++) {
        l.push(new ReportedIssue(json["list"][i]));
      }
      this.list = l;
    }
  }
}

export class IssuesListError extends IssuesList {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}

export const ISSUES_PER_PAGE = 20;
