import { Component, Input } from '@angular/core';

import { SECTIONS } from './sections-list';
import { TEXT_DIVISIONS, TEXT_DEFAULT_VALUES, NUM_TEXT_DIVISIONS } from '../lexicon-info';

@Component({
  selector: 'sections-browse',
  templateUrl: './browse-sections.component.html',
  styleUrls: [ './browse-sections.component.css' ]
})

export class BrowseSectionsComponent{
  @Input() public displayType: string;
  @Input() public openSet: string[];
  @Input() public level: number;

  public displayTypes: string[] = TEXT_DIVISIONS;

  public textList = SECTIONS;

  public open: string[] = TEXT_DEFAULT_VALUES;

  // get the display type
  getDisplayType() {
    return this.displayType;
  }

  // on initialization and changes, update open panels
  ngOnInit(): void {
    this.newPage()
  }

  ngOnChanges(): void {
    this.newPage()
  }

  // when there is a new page, set the location to the proper place
  newPage(): void {
    // Set location to this one
    for (let i = 0; i < NUM_TEXT_DIVISIONS; i++) {
      this.open[i] = this.openSet[i];
    }
  }

  // True if there is a subsection below this
  hasSublist(current: string): boolean {
    return !(this.displayType == this.displayTypes[this.level]) && (this.open[this.level] == current);
  }

  // True if we should only show the part associated with this level
  showPartOnly(): boolean {
    return this.displayType == this.displayTypes[this.level];
  }

  // Get router link for a part
  getRouterLink(curr: string): string {
    let parts = this.open.slice(0, this.level);
    parts.push(curr);
    let destination = parts.join(".");
    return "/text/" + destination;
  }

  // Get link text for a part
  getLinkText(curr: number): string {
    return this.displayTypes[this.level] + " " + curr;
  }

  // Open the given list
  openList(curr: string): void {
    if (curr == this.open[this.level]) {
      this.open[this.level] = curr + "-";
    } else if (curr + "-" == this.open[this.level]) {
      this.open[this.level] = curr;
    } else {
      this.open[this.level] = curr;
      if (this.level + 2 < NUM_TEXT_DIVISIONS) {
        this.open[this.level+1] = "-1";
      }
    }
  }

  // remove _ items from the array of keys; it should be the last four
  getListItems() {
    let arr = this.textList;
    for (let i = 0; i < this.level; i++) {
      arr = arr[this.open[i]];
    }

    return Object.keys(arr).slice(0, -4);
  }

  // given an integer, create a fake array to allow an *ngFor
  fakeArray(size: number): number[] {
    let ret = new Array(size);
    return ret;
  }
}
