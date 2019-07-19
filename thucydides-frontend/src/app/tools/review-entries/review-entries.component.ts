import { Component } from '@angular/core';

import { Router, NavigationEnd } from '@angular/router';

import { ReviewEntries } from './entries-to-review';
import { BackendService } from '../../backend.service';

@Component({
  selector: 'review-entries',
  templateUrl: './review-entries.component.html',
  styleUrls: [ './review-entries.component.css' ]
})

export class ReviewEntriesComponent{
  // is this loading?
  public isLoading = true;

  // information on the lemmata
  public info: ReviewEntries;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  constructor(
    private router: Router,
    private backendService: BackendService
  ) {}

  ngOnInit(): void {
    this.updateList();

    // every time the route is updated to a new stem, change the data
    // this lets us flip straight from a token to its stem
    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        this.updateList();
      }
    });
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the lemma
  updateList(): void {
    this.isLoading = true;
    let observation = this.backendService.getEntriesToReview(); //0, false
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: ReviewEntries): void {
    this.isLoading = false;
    this.info = results;
  }

  getEntryLink(e: string): string {
    return "/entry/" + e;
  }
}
