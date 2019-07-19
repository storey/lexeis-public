import { Component, Input } from '@angular/core';

import { Router } from '@angular/router';

import { LoginInfoService } from "../../login-info.service";
import { LoginInfo, LOGIN_INFO_DEFAULT } from '../../user/login-info';

import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';

@Component({
  selector: 'report-issue',
  templateUrl: './report-issue.component.html',
  styleUrls: [ '../form-styles.css' ]
})

export class ReportIssueComponent{
  // default location for the issue
  @Input() useLocation: boolean;

  // is the user logged in?
  public loginInfo: LoginInfo = LOGIN_INFO_DEFAULT;

  // how many characters has the user added?
  public commentCharacters = 0;

  public MAX_COMMENT_CHARACTERS = 800;
  public WARNING_COMMENT_CHARACTERS = 20;

  public userEmail: string = "";
  public issueLocation: string = "";
  public issueComment: string = "";

  public loading = false;

  public report: ChangeReport = new ChangeReportDefault();

  constructor(
    private router: Router,
    private backendService: BackendService,
    private login: LoginInfoService
  ) {}

  ngOnInit(): void {
    // stay up to date with login info
    this.login.currentLoginInfo.subscribe(loginInfo => this.loginInfo = loginInfo);

    this.resetFormValues();
  }

  // Reset the user input values
  resetFormValues():void {
    // If they are reporting based on the bottom of the page, pre-fill the url.
    if (this.useLocation) {
      this.issueLocation = decodeURI(this.router.url);
    } else {
      this.issueLocation = "";
    }

    this.issueComment = "";
  }

  // Start a new issue
  newIssue(): void {
    this.resetFormValues();
    this.report = new ChangeReportDefault();
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
      if (!this.loginInfo.loggedIn && controls.email.pristine) {
        controls.email.markAsTouched();
      }
      if (controls.location.pristine) {
        controls.location.markAsTouched();
      }
      if (controls.comment.pristine) {
        controls.comment.markAsTouched();
      }
    } else {
      let e = "";
      if (!this.loginInfo.loggedIn) {
        e = controls.email.value;
      }
      let l = controls.location.value;
      let c = controls.comment.value;

      this.loading = true;

      let observation = this.backendService.reportIssue(e, l, c);
      observation.subscribe(results => this.handleResults(results));
    }
  }

  // Handle results of submission
  handleResults(results: ChangeReport) {
    this.loading = false;
    this.report = results;
  }
}
