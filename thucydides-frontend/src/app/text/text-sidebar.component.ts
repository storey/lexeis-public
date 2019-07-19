import { Component, Input, Output, EventEmitter, Inject, HostListener } from '@angular/core';
import { DOCUMENT } from "@angular/common";
import { CONTEXT_ARRAY, TEXT_DIVISIONS } from '../lexicon-info';

@Component({
  selector: 'text-sidebar',
  templateUrl: './text-sidebar.component.html',
  styleUrls: [ './text-sidebar.component.css' ]
})

export class TextSidebarComponent{
  @Input() public displayType: string;
  @Input() public contextDisplayType: string;
  @Output() changeContextDisplay = new EventEmitter<string>();
  @Input() public locationSpec: number[];

  public displayTypes: string[] = TEXT_DIVISIONS;

  public contextDisplayTypes: string[][] = [
    ["Off", ""],
    ["On", "show-context-bg"]
  ];

  public contexts = CONTEXT_ARRAY;

  // store whether we have scrolled past viewing the sidebar
  public scrolledDown: boolean = false;

  // from
  // https://stackoverflow.com/questions/44144085/angular-2-change-position-to-fixed-on-scroll
  // https://brianflove.com/2016/10/10/angular-2-window-scroll-event-using-hostlistener/
  constructor(@Inject(DOCUMENT) private doc: Document) {}

  @HostListener("window:scroll", [])
  onWindowScroll() {
    let scroll = this.doc.documentElement.scrollTop || this.doc.body.scrollTop || 0;
    if (scroll > 690) {
      this.scrolledDown = true;
    } else {
      this.scrolledDown = false;
    }
  }

  // get the link for the "group by" button
  getRouterLink(type: string) {
    if (this.displayType === type) {
      return null;
    } else {
      let level = this.displayTypes.indexOf(type);
      let loc = this.locationSpec.slice(0,level+1).join(".");
      return "/text/" + loc;
    }
  }


  // given a type, return the classes associated with the "group by" button
  // for that type
  getTypeClasses(type: string) {
    if (this.displayType === type) {
      return "btn-primary";
    } else {
      return "btn-outline-primary";
    }
  }

  // get the display type
  getDisplayType() {
    return this.displayType;
  }

  // true if this is the largest display type
  isTopDisplayType() {
    return this.displayType == TEXT_DIVISIONS[0];
  }


  // given a context display type, return the classes associated with the "group by" button
  // for that type
  getContextTypeClasses(type: string) {
    if (this.contextDisplayType === type[1]) {
      return "btn-primary";
    } else {
      return "btn-outline-primary";
    }
  }

  // set context display type
  setContextDisplayType(type: string[]) {
    this.changeContextDisplay.emit(type[1]);
  }

  // get the proper class for displaying context
  getContextDisplayClass() {
    return this.contextDisplayType;
  }
}
