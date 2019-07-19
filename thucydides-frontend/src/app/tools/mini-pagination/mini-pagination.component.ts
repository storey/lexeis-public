import { Component, Input } from '@angular/core';

import { PaginationList } from './pagination-list';

@Component({
  selector: 'mini-pagination',
  templateUrl: './mini-pagination.component.html',
  styleUrls: [ './mini-pagination.component.css' ]
})

export class MiniPaginationComponent{
  // information for the lemma
  @Input() list: PaginationList<any>;

  // Current page
  @Input() page: number = -1;

  // Path to pages of this type
  @Input() basePath: string;

  constructor() {}

  ngOnInit(): void {}

  // Get the title for this page
  getTitle(): string {
    let baseline = this.page*this.list.perPage + 1;
    let end = baseline + this.list.list.length -1;
    return "Viewing " + this.list.itemName + " " + baseline + "-" + end + " (out of " + this.list.size + ")";
  }

  // Get the last page available
  getLastPage(): number {
    return Math.max(0, Math.floor((this.list.size-1)/this.list.perPage));
  }

  // Get the link to the first page.
  getFirstPageLink(): string {
    let p = 0;
    return this.basePath + p;
  }

  // Get the link to the previous page
  getPrevPageLink(): string {
    let p = this.page - 1;
    if (p < 0) {
      p = 0;
    }
    return this.basePath + p;
  }

  // Get the link to the next page
  getNextPageLink(): string {
    let last = this.getLastPage();
    let p = this.page + 1;
    if (p > last) {
      p = last;
    }
    return this.basePath + p;
  }

  // Get the link to the last page
  getLastPageLink(): string {
    let p = this.getLastPage();
    return this.basePath + p;
  }

  // True if we are on the first page
  onFirstPage(): boolean {
    return this.page === 0;
  }

  // True if we are on the last page
  onLastPage(): boolean {
    return this.page === this.getLastPage();
  }
}
