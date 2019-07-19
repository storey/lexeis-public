// list of changes
import { PaginationList } from '../mini-pagination/pagination-list';

export class ChangeItem {
  id: number;
  user: string;
  tstamp: string;
  change_type: number;
  context: string;
  before_value: string;
  after_value: string;
  change_type_readable: string;
  context_readable: string;
  before_value_readable: string;
  after_value_readable: string;

  // Get a description of how to undo this change
  public undoDescription(): string {
    if (this.change_type == 1 || this.change_type == 2) { // Add new article, Add edited article
      return "Adding a new article cannot be undone. Just reject the article using the link below.";
    } else if (this.change_type == 3) { // Reject article
      let msg = "If an article was improperly rejected, go to the article link below, ";
      msg += "copy the raw article, and submit a new article for the lemma using the ";
      msg += "same raw text."
      return msg;
    } else if (this.change_type == 4) { // Accept article
      let msg = "If this article was improperly accepted, you will need to restore ";
      msg += "the previous article (linked below). Go to the previous article, ";
      msg += "copy the raw article, and submit a new article for the lemma using ";
      msg += "the same raw text.";
      return msg;
    } else if (this.change_type == 5) { // Assign article
      let msg = "If this article was improperly assigned, go to the page for  ";
      msg += "assigning article (linked below), view articles assigned to ";
      msg += this.after_value_readable;
      msg += ", select the incorrectly assigned article, and use the dropdown ";
      msg += "to assign it to the correct individual.";
      return msg;
    } else if (this.change_type == 6) { // Add a lemma
      let msg = "If this lemma was incorrectly added, delete it using the link below.";
      return msg;
    } else if (this.change_type == 7) { // Delete a lemma (this is done via button)
      return "";
    } else if (8 <= this.change_type && this.change_type <= 18) { // Change lemma things
      let msg = "To undo this change, click the link below to edit the lemma and ";
      msg += "set the appropriate field to the previous value \"";
      msg += this.before_value_readable + "\".";
      return msg;
    } else if (this.change_type == 19) { // Change lemma's status
      let msg = "There isn't an easy way to undo a change in lemma status; just ";
      msg += "make sure you proofread it enough that you are sure it meets the ";
      msg += "criteria for its status.";
      return msg;
    } else if (this.change_type == 20) { // Add a compound
      let msg = "If this compound was incorrectly added, delete it using the link below.";
      return msg;
    } else if (this.change_type == 21) { // Delete a compound (this is done via button)
      return "";
    } else if (this.change_type == 22 || this.change_type == 23) { // Change a compound's name or description
      let msg = "To undo this change, click the link below to edit the compound and ";
      msg += "set the appropriate field to the previous value \"";
      msg += this.before_value_readable + "\".";
      return msg;
    } else if (this.change_type == 24) { // Add a root
      let msg = "If this root was incorrectly added, delete it using the link below.";
      return msg;
    } else if (this.change_type == 25) { // Delete a root (this is done via button)
      return "";
    } else if (this.change_type == 26 || this.change_type == 27) { // Change a root's name or description
      let msg = "To undo this change, click the link below to edit the root and ";
      msg += "set the appropriate field to the previous value \"";
      msg += this.before_value_readable + "\".";
      return msg;
    } else if (this.change_type == 28) { // Add a semantic group
      let msg = "If this semantic group was incorrectly added, delete it using the link below.";
      return msg;
    } else if (this.change_type == 29) { // Delete a semantic group (this is done via button)
      return "";
    } else if (this.change_type == 30 || this.change_type == 31 || this.change_type == 32) { // Change a semantic group's name or label type or description
      let msg = "To undo this change, click the link below to edit the semantic group and ";
      msg += "set the appropriate field to the previous value \"";
      msg += this.before_value_readable + "\".";
      return msg;
    } else if (this.change_type == 33 || this.change_type == 34 || this.change_type == 35) { // Change a token's lemma, meaning, or context
      let msg = "To undo this change, click the link below to edit the token and ";
      msg += "set the appropriate field to the previous value \"";
      msg += this.before_value_readable + "\".";
      return msg;
    } else if (this.change_type == 36) { // Add an alias
      let msg = "If this alias was incorrectly added, delete it using the link below.";
      return msg;
    } else if (this.change_type == 37) { // Delete an alias
      let msg = "If this alias was incorrectly deleted, add it back using the link below.";
      return msg;
    } else if (this.change_type == 38 || this.change_type == 39) { // Change an alias' alias or lemma
      let msg = "To undo this change, click the link below to edit the alias and ";
      msg += "set the appropriate field to the previous value \"";
      msg += this.before_value_readable + "\".";
      return msg;
    }
  }

  // True if this can be undone via the server
  public serverUndo(): boolean {
    // Don't show a link for the deletes
    if (this.change_type == 7 || this.change_type == 21 || this.change_type == 25 || this.change_type == 29) {
      return true;
    }
    return false;
  }

  // True if the undo has an associated link
  public hasLink(): boolean {
    // Don't show a link for the deletes
    if (this.change_type == 7 || this.change_type == 21 || this.change_type == 25 || this.change_type == 29) {
      return false;
    }
    return true;
  }

  // Return the router link that will allow the user to undo this change
  public routerLink(): string {
    // Skip: Delete a lemma (7), Delete a compound (21), Delete a root (25), Delete a semantic group (29)

    if (1 <= this.change_type && this.change_type <= 3) { // Add new article, Add edited article, Reject article
      return "/tools/articleDraft/" + this.context;
    } else if (this.change_type == 4) { // Accept article
      return "/tools/articleDraft/" + this.before_value;
    } else if (this.change_type == 5) { // Assign article
      return "tools/unwrittenArticles/0";
    } else if (this.change_type == 6 || 8 <= this.change_type && this.change_type <= 18) { // Add a lemma, change lemma fields
      let splt1 = this.context_readable.split("lemma: ")
      let splt2 = splt1[1].split(" (");
      let lem = splt2[0];
      return "/tools/editLemma/" + lem;
    } else if (this.change_type == 19) { // Change lemma's status
      return "/entry/" + this.context_readable;
    } else if (this.change_type == 20 || this.change_type == 22 || this.change_type == 23) { // Add or edit a compound
      let loc = this.context_readable.split(" (")[0];
      return "/tools/editCompoundPart/" + loc;
    } else if (this.change_type == 24 || this.change_type == 26 || this.change_type == 27) { // Add or edit a root
      let loc = this.context_readable.split(" (")[0];
      return "/tools/editRoot/" + loc;
    } else if (this.change_type == 28 || this.change_type == 30 || this.change_type == 31 || this.change_type == 32) { // Add or edit a semantic group
      return "/tools/editSemanticGroup/" + this.context;
    } else if (this.change_type == 33 || this.change_type == 34 || this.change_type == 35) { // Change a token
      return "/tools/editToken/" + this.context;
    } else if (this.change_type == 36 || this.change_type == 38 || this.change_type == 39) { // Change an alias
      let splt1 = this.context_readable.split("alias: ")
      let splt2 = splt1[1].split(" (");
      let alias = splt2[0];
      return "/tools/editAlias/" + alias;
    } else if (this.change_type == 37) { // Add an alias
      return "/tools/addAlias/";
    }
  }

  // Return the text for the link that will allow the user to undo this change
  public linkText(): string {
    // Skip: Delete a lemma (7), Delete a compound (21), Delete a root (25), Delete a semantic group (29)

    if (1 <= this.change_type && this.change_type <= 3) { // Add new article, Add edited article, Reject article
      return "Article " + this.context;
    } else if (this.change_type == 4) { // Accept article
      return "Article " + this.before_value;
    } else if (this.change_type == 5) { // Assign article
      return "Unwritten Articles List";
    } else if (this.change_type == 6 || 8 <= this.change_type && this.change_type <= 18) { // Add a lemma, change lemma fields
      return "Edit/Delete " + this.context_readable;
    } else if (this.change_type == 19) { // Change lemma's status
      return "View Entry for " + this.context_readable;
    }  else if (this.change_type == 20 || this.change_type == 22 || this.change_type == 23) { // Add or edit a compound
      return "Edit/Delete Compound " + this.context_readable;
    } else if (this.change_type == 24 || this.change_type == 26 || this.change_type == 27) { // Add or edit a root
      return "Edit/Delete Root " + this.context_readable;
    } else if (this.change_type == 28 || this.change_type == 30 || this.change_type == 31 || this.change_type == 32) { // Add or edit a semantic group
      return "Edit/Delete Semantic Group " + this.context_readable;
    } else if (this.change_type == 33 || this.change_type == 34 || this.change_type == 35) { // Change a token
      return "Edit Token " + this.context_readable;
    } else if (this.change_type == 36 || this.change_type == 38 || this.change_type == 39) { // Change an alias
      return "Edit/Delete " + this.context_readable;
    } else if (this.change_type == 37) { // Add an alias
      return "Add a New Alias";
    }
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class ChangeList extends PaginationList<ChangeItem> {
  // Information for displaying a single page of these
  perPage: number = CHANGES_PER_PAGE;
  itemName: string = "Changes";

  constructor(json: any) {
    super(json);

    // Each change item needs to be initialized as an object
    if ("list" in json) {
      let c: ChangeItem[] = [];
      for (let i = 0; i < json["list"].length; i++){
        c.push(new ChangeItem(json["list"][i]));
      }
      this.list = c;
    }
  }
}

export class ChangeListError extends ChangeList {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}


export const CHANGES_PER_PAGE = 20;

// information for the lists
export class User {
  id: number;
  name: string;
}

export class ChangeType {
  id: number;
  name: string;
}

export class ChangeLogInfo {
  isError: boolean;
  message: string;
  users: User[];
  changeTypes: ChangeType[];

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

export class ChangeLogInfoError extends ChangeLogInfo {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}
