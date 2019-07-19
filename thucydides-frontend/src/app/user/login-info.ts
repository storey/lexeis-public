// Stores information about the user's login
export class LoginInfo {
  loggedIn: boolean;
  firstName: string;
  id: number;
  accessLevel: number;
}

export const LOGIN_INFO_ERROR: LoginInfo = {
  loggedIn: false,
  firstName: "",
  id: -1,
  accessLevel: -1,
}

export const LOGIN_INFO_DEFAULT: LoginInfo = {
  loggedIn: false,
  firstName: "User",
  id: -1,
  accessLevel: 0,
}

// Store information for the user page
export class UserPageInfo {
  isError: boolean;
  message: string;
  assignedArticles: number;
  unwrittenArticles: number;
  unapprovedDrafts: number;
  entriesToProofread: number;
  entriesToFinalize: number;
  unresolvedIssues: number;

  // True if this is an error
  error(): boolean {
    return this.isError;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class UserPageInfoError extends UserPageInfo {
  constructor() {
    super({});
    this.isError = true;
    this.message = "There was an error communicating with the server. Please check your internet connection.";
  }
}
