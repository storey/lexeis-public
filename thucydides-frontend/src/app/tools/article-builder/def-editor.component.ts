import { Component, Input, OnChanges } from '@angular/core';

import { extractListAndIssues } from './build-article';

@Component({
  selector: 'def-editor',
  templateUrl: './def-editor.component.html',
  styleUrls: [ './def-editor.component.css' ]
})

export class DefEditorComponent implements OnChanges {
  // information for the lemma
  @Input() lemma: string;
  // information for the lemma
  @Input() occurrences: string[][];
  // hold the current article
  @Input() currentRawArticle: string;

  // true if this article is being built for the first time,
  // false if it is being viewed by an editor
  isBuilding: boolean = true;

  // True if article is editable
  @Input() isEditable: boolean;

  // True if we show the raw article
  @Input() showRaw: boolean;

  // for editor, we hide the editing area by default
  public hideEditArea: boolean = true;

  public rawArticle: string = "";

  // info for article preview
  public previewDef: any = null;

  private validRefs = {};
  private userRefs = [];
  public badRefs = [];

  public warningKPs = [];
  public errorKPs = [];

  constructor() {}

  ngOnInit(): void {
    this.resetEditor();
  }

  ngOnChanges() {
    this.resetEditor();
  }

  // reset the article based on input
  resetEditor(): void {
    this.rawArticle = this.currentRawArticle;

    this.validRefs = {};
    for (let i = 0; i < this.occurrences.length; i++) {
      this.validRefs[this.occurrences[i][0]] = true;
    }

    this.generatePreview(null);
  }

  // given raw input, convert to a preview
  generatePreview(event: any) {
    // this.rawArticle = event.target.value;
    this.previewDef = this.createLongDefObjects(this.rawArticle);
  }

  // Divide a raw definition into the multiple options
  createLongDefObjects(raw: string) {
    let options = raw.split("======---");

    // if no options, just parse the one and return it
    if (options.length == 1) {
      return [this.createLongDefObject(raw)];
    }

    // otherwise parse individual pieces
    let longDef = [];
    for (let i = 1; i < options.length; i++) {
      let split = options[i].split("---======");
      let def = this.createLongDefObject(split[1]);
      def["defType"] = split[0];
      longDef.push(def);
    }
    return longDef;
  }

  // given a raw long definition description, convert it into an object that
  // holds the structure more specifically.
  createLongDefObject(raw) {
    raw = raw.replace(/[“”]/g, "\"");//raw.replace("”", "\"").replace("“", "\"");
    let meanings = {};
    let result = extractListAndIssues(raw, 1, "", meanings);
    this.userRefs = result["problems"]["userRefs"];
    this.warningKPs = result["problems"]["warningKPs"];
    this.errorKPs = result["problems"]["errorKPs"];
    let defObj = result["list"];

    // keep track of bad references
    this.badRefs = [];
    for (let i = 0; i < this.userRefs.length; i++) {
      let ref = this.userRefs[i];
      if (!this.validRefs[ref]) {
        this.badRefs.push(ref);
      }
    }
    return defObj;
  }

  // is there a warning or error
  defHasProblem() {
    return this.defHasError() || this.defHasWarning();
  }

  // is there an error
  defHasError() {
    let badKeyPassage = this.errorKPs.length > 0;
    return badKeyPassage;
  }

  // is there a warning
  defHasWarning() {
    let badRefs = this.badRefs.length > 0;
    let badKeyPassage = this.warningKPs.length > 0;
    return badRefs || badKeyPassage;
  }

  // Get error text for the article draft
  getArticleErrorText() {
    let errs = [];
    if (this.errorKPs.length > 0) {
      for (let i = 0; i < this.errorKPs.length; i++) {
        let e = this.errorKPs[i];
        errs.push("Error parsing key passage at " + e[0] + " (Exact text is “" + e[1] + "”). Key passages should be of the form 1.2.3 Greek Text \"Translation\"");
      }
    }
    return errs;
  }

  // Get warning text for the draft
  getArticleWarningText() {
    let warns = [];
    if (this.badRefs.length > 0) {
      warns.push("The lemma " + this.lemma + " does not occur at the following locations: " + this.getBadLocations());
    }
    if (this.warningKPs.length > 0) {
      for (let i = 0; i < this.warningKPs.length; i++) {
        let w = this.warningKPs[i];
        if (w[1] == "Greek") {
          warns.push("Key Passage Greek is empty at " + w[0]);
        }
        else if (w[1] == "English") {
          warns.push("Key Passage Translation is empty at " + w[0]);
        }
      }
    }
    return warns;
  }

  // return the list of bad locations
  getBadLocations() {
    return this.badRefs.join(", ");
  }

  // get the order of the edit section
  getEditOrder(): string {
    if (this.isEditable) {
      return "order-1";
    } else {
      return "order-3";
    }
  }

  // get the order of the preview section
  getPreviewOrder(): string {
    if (this.isEditable) {
      return "order-3";
    } else {
      return "order-1";
    }
  }

  // get title for the edit section
  getEditTitle(): string {
    if (this.isEditable) {
      return "Article Draft:";
    } else {
      return "Edit Article:";
    }
  }

  // get title for the preview section
  getPreviewTitle(): string {
    if (this.isEditable) {
      return "Preview:";
    } else {
      return "Article Preview:";
    }
  }

  // true if we should display a button to show the edit area,
  // false if we should show the edit area
  showEditButton(): boolean {
    if (this.isBuilding) {
      return false;
    } else if (this.hideEditArea) {
      return true;
    }
    return false;

  }

  // reveal the editing area
  activateEditing(): void {
    this.hideEditArea = false;
  }
}
