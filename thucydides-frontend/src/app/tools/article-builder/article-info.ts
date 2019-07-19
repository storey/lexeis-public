// all the info in an entry, plus a little extra
import { FrequencyHolder } from "../../entry/entry";

export class ArticleInfo {
  token: string;
  id: number;
  shortDef: string; // also contains error messages when necessary

  search: string[];

  hasLongDefinition: boolean;
  oldLongDefinition: boolean;
  fullDefinition: any;
  rawFullDefinition: string;

  partOfSpeech: string;
  semanticGroups: number[];
  stemType: string[];
  compoundParts: string[];
  frequency: FrequencyHolder;

  hasKeyPassage: boolean;
  keyPassageLocation: string;
  keyPassageText: string;
  keyPassageTranslation: string;

  hasIllustration: boolean;
  illustrationLink: string;
  illustrationAlt: string;
  illustrationCaption: string;

  bibliographyText: string;

  occurrences: string[][];

  priorArticle: boolean; // was there a prior article?
  priorAuthor: string; // what was the prior author name?
  priorAuthorID: number; // what was the prior author id?
  priorCustomAuthor: string; // what was prior custom article


  articlePending: boolean;
  assignedToUser: boolean;
  lemmaStatus: number;

  // True if this is an error
  isError(): boolean {
    return this.token == "ERROR_TOKEN";
  }

  // If this is an error, return the associated error message
  getErrorText(): string {
    return this.shortDef;
  }

  constructor(json: any) {
    for(var key in json) {
      this[key] = json[key];
    }
  }
}

export class ArticleInfoError extends ArticleInfo {
  constructor() {
    super({});
    this.token = "ERROR_TOKEN";
    this.shortDef = "There was an error communicating with the server. Please check your internet connection.";
  }
}
