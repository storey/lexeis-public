import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { IssuesList, ISSUES_PER_PAGE } from './issues-list';
import { BackendService } from '../../backend.service';

import { boolToEnglish } from '../../globals';


@Component({
  selector: 'issues-list',
  templateUrl: './issues-list.component.html',
  styleUrls: [ './issues-list.component.css' ]
})

export class IssuesListComponent{
  // is this loading?
  public isLoading = true;

  // information for the lemma
  public issues: IssuesList;

  public page = -1;

  public show_resolved = false;

  public issuesPerPage = ISSUES_PER_PAGE;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  // Base paths for viewing unresolved/all issues
  private BASE_PATH_UNRESOLVED = "/tools/reportedIssues/0/";
  private BASE_PATH_RESOLVED = "/tools/reportedIssues/1/";

  // Used in HTML
  private basePath = this.BASE_PATH_UNRESOLVED;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService
  ) {}

  ngOnInit(): void {
    this.updateChanges(this.route.snapshot.paramMap);

    // every time the route is updated to a new stem, change the data
    // this lets us flip straight from a token to its stem
    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        let params = this.route.snapshot.paramMap;
        this.updateChanges(params);
      }
    });
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the lemma
  updateChanges(params: ParamMap): void {
    this.page = +params.get('page');
    if (+params.get('showResolved') == 1) {
      this.show_resolved = true;
      this.basePath = this.BASE_PATH_RESOLVED;
    } else {
      this.show_resolved = false;
      this.basePath = this.BASE_PATH_UNRESOLVED;
    }
    this.isLoading = true;
    let observation = this.backendService.getReportedIssues(this.page, this.show_resolved);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: IssuesList): void {
    this.isLoading = false;
    this.issues = results;
  }

  // get classes for radio button
  getResolvedRadioClass(button: boolean): string {
    if (button == this.show_resolved) {
      return "btn btn-primary";
    } else {
      return "btn btn-outline-primary";
    }
  }

  // Set whether we are showing resolved or unresolved issues only
  setShowResolved(val: boolean): void {
    if (val != this.show_resolved) {
      if (val) {
        this.router.navigate([this.BASE_PATH_RESOLVED]);
      } else {
        this.router.navigate([this.BASE_PATH_UNRESOLVED]);
      }
    }
  }

  // Convert boolean to human-readable text
  getResolvedText(resolved: boolean): string {
    return boolToEnglish(resolved);
  }

  // Given a comment of arbitrary length, clip it
  clipComment(comment: string): string {
    let clipLen = 15;
    if (comment.length > 15) {
       return comment.substring(0, clipLen-3) + "...";
    }
    return comment;
  }
}
