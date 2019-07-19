import { Component, Input } from '@angular/core';

import { Entry } from './entry';

import { CONTEXT_ARRAY } from '../lexicon-info';

@Component({
  selector: 'lemma-detail',
  templateUrl: './lemma-detail.component.html',
  styleUrls: [ './lemma-detail.component.css' ]
})

export class LemmaDetailComponent{
  @Input() myEntry: Entry;
  public contexts = CONTEXT_ARRAY;

  private TABLE_SHOW = "(show " + (this.contexts.length-3) + " more)";
  private TABLE_HIDE = "(hide)";

  public showTableText = this.TABLE_SHOW;

  // if true, show full table; if false, show first 4 items
  public showFullTable = false;

  // Toggle showing table
  toggleTable(): void {
    this.showFullTable = !this.showFullTable;
    if (this.showFullTable) {
      this.showTableText = this.TABLE_HIDE;
    } else {
      this.showTableText = this.TABLE_SHOW;
    }
  }

  // Get context list based on whether we are showing full table or not
  getContextList(): string[] {
    if (this.showFullTable || this.contexts.length <= 4) {
      return this.contexts;
    }
    return this.contexts.slice(0, 3);
  }
}
