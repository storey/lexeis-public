import { Component, Input } from '@angular/core';

import { Observable } from 'rxjs';

import { PreppedText } from './prepped-text';
import { SECTIONS } from './sections-list';
import { TEXT_DIVISIONS } from '../lexicon-info';

@Component({
  selector: 'section-display',
  templateUrl: './section-display.component.html',
  styleUrls: [ './section-display.component.css' ]
})

export class SectionDisplayComponent{
  @Input() public displayType: string;
  @Input() public contextDisplayType: string;
  @Input() public locationSpec: number[];
  @Input() public lemma: string;

  @Input() public displayText: PreppedText;
  @Input() public busy: Observable<PreppedText>;
  @Input() public isLoading: boolean;

  // Get the item before the current location spec given the specified
  // level of depth
  getPrev(level: number): string[] {
    // If there are no previous items, return empty string
    if (level == -1) {
      return [""];
    }
    // get object with info on current part of text
    let item = SECTIONS;
    for (let i = 0; i <= level; i++) {
      item = item[this.locationSpec[i]];
    }
    // if this is the earliest part of this level,
    // We have to go one back at the higher level
    // e.g. if this is the first chapter in the book we need the last
    // chapter in the previous book
    if (item["_prev"] == "") {
      let prevParent = this.getPrev(level - 1);
      if (prevParent[0] == "") {
        return [""];
      }
      let curr = SECTIONS;
      for (let i = 0; i < prevParent.length; i++) {
        curr = curr[prevParent[i]];
      }
      let me = curr["_last"];
      prevParent.push("" + me);
      return prevParent;
    }
    // otherwise just decrement by one
    let loc = [];
    for (let i = 0; i < level; i++) {
      loc.push(this.locationSpec[i]);
    }
    loc.push(item["_prev"]);
    return loc;
  }

  // Get the item after the current location spec given the specified
  // level of depth
  getNext(level: number): string[] {
    // If there are no previous items, return empty string
    if (level == -1) {
      return [""];
    }
    // get object with info on current part of text
    let item = SECTIONS;
    for (let i = 0; i <= level; i++) {
      item = item[this.locationSpec[i]];
    }
    // if this is the last part of this level,
    // We have to go one forward at the higher level
    // e.g. if this is the last chapter in the book we need the first
    // chapter in the next book
    if (item["_next"] == "") {
      let nextParent = this.getNext(level - 1);
      if (nextParent[0] == "") {
        return [""];
      }
      let curr = SECTIONS;
      for (let i = 0; i < nextParent.length; i++) {
        curr = curr[nextParent[i]];
      }
      let me = curr["_first"];
      nextParent.push("" + me);
      return nextParent;
    }
    // otherwise just decrement by one
    let loc = [];
    for (let i = 0; i < level; i++) {
      loc.push(this.locationSpec[i]);
    }
    loc.push(item["_next"]);
    return loc;
  }

  // get previous based on display type type
  getPrevPath(): string {
    let level = TEXT_DIVISIONS.indexOf(this.displayType);
    let prev =  this.getPrev(level);
    if (prev[0] == "") {
      return "";
    }
    return prev.join(".");
  }

  // get next based on display type
  getNextPath(): string {
    let level = TEXT_DIVISIONS.indexOf(this.displayType);
    let next =  this.getNext(level);
    if (next[0] == "") {
      return "";
    }
    return next.join(".");
  }

  // get title for the text displayed on this page
  getTextTitle(): string {
    let level = TEXT_DIVISIONS.indexOf(this.displayType);

    if (level == 0) {
      return TEXT_DIVISIONS[0] + " " + this.locationSpec[0];
    }

    let pieces = this.locationSpec.slice(0, level+1);
    return pieces.join(".");
  }

  // Get the html associated with this text
  getTextHTML() {
    if (this.displayText) {
      return this.displayText.rawHTML;
    } else {
      return "";
    }
  }

}
