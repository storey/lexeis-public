import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { ReportedIssue } from './issues-list';
import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';


@Component({
  selector: 'view-issue',
  templateUrl: './view-issue.component.html',
  styleUrls: [ './view-issue.component.css', '../form-styles.css' ]
})

export class ViewIssueComponent{
  // is this loading?
  public isLoading = true;

  // information for the lemma
  public issue: ReportedIssue;

  // Issues id
  public id: number = 0;

  // how many characters has the user added?
  public commentCharacters = 0;

  public MAX_COMMENT_CHARACTERS = 800;
  public WARNING_COMMENT_CHARACTERS = 20;

  // user comment on issue
  public issueComment: string = "";


  // Report and loading variable for the report on resolving the issue
  public report: ChangeReport = new ChangeReportDefault();

  public loadingResolveResult: boolean = false;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

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
    let issueID = +params.get('id');
    this.id = issueID;
    let observation = this.backendService.getIssue(issueID);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: ReportedIssue): void {
    this.isLoading = false;
    this.issue = results;
  }

  // get appropriate card class based on whether issue is resolved
  getCardClass(resolved: boolean): string {
    if (resolved) {
      return "card " + "border-success";
    } else {
      return "card " + "border-danger";
    }
  }

  // Count the number of characters in the comment
  countCommentCharacters(event: any) {
    this.commentCharacters = event.target.value.length;
  }

  // get the classes for displaying the "x characters left"
  getCommentLengthWarningClasses(): string {
    let charsLeft = this.MAX_COMMENT_CHARACTERS - this.commentCharacters;

    let classes = "mt-2 text-right";
    if (charsLeft == 0) {
      classes += " text-danger";
    } else if (charsLeft <= this.WARNING_COMMENT_CHARACTERS) {
      classes += " text-warning";
    } else {
      return "display-none";
    }
    return classes;
  }

  // get the "x characters left" text
  getCommentLengthWarning(): string {
    let charsLeft = this.MAX_COMMENT_CHARACTERS - this.commentCharacters;
    return charsLeft + " characters left.";
  }

  // submit form
  onSubmit(form: any): void {
    let controls = form.form.controls;
    // if form is invalid, prevent submission and show error messages.
    if (!form.valid) {
      if (controls.comment.pristine) {
        controls.comment.markAsTouched();
      }
    } else {
      let c = controls.comment.value;

      this.loadingResolveResult = true;

      let observation = this.backendService.resolveIssue(this.id, c);
      observation.subscribe(results => this.handleResolveResults(results));
    }
  }

  // Handle results of submission
  handleResolveResults(results: ChangeReport) {
    this.loadingResolveResult = false;
    this.report = results;

    if (this.report.isSuccess()) {
      let params = this.route.snapshot.paramMap;
      this.updateChanges(params);
    }
  }

}
