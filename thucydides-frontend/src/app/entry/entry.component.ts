import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import {NgbModal} from '@ng-bootstrap/ng-bootstrap';

import { Entry } from './entry';
import { BackendService } from '../backend.service';

import { ChangeReport, ChangeReportDefault } from "../tools/change-report";
import { LoginInfoService } from "../login-info.service";
import { LoginInfo, LOGIN_INFO_DEFAULT } from '../user/login-info';
import { CONTEXT_NAMES_SHORT } from '../lexicon-info';

@Component({
  selector: 'entry',
  templateUrl: './entry.component.html',
  styleUrls: [ './entry.component.css' ]
})

export class EntryComponent{
  private MORE_TEXT = "(more)";
  private LESS_TEXT = "(less)";
  private SHOW_TEXT = "(show)";
  private HIDE_TEXT = "(hide)";

  private STATUSES: string[] = [
    "Unreviewed",
    "Draft",
    "Awaiting Final Proof",
    "Final"
  ];

  // is this loading?
  public isLoading = true;

  // Stores entry data
  public entryData: Entry;

  // for handling collapsing and uncollapsing

  // lemma
  public lemmaCollapsed = true;
  public lemmaCollapseText = this.MORE_TEXT;

  // definition
  public definitionCollapsed = true;
  public definitionCollapseText = this.MORE_TEXT;
  public definitionTitle = "Short Definition";

  // the specified meaning
  public targetMeaning: string = null;

  // occurrences
  public occurrencesCollapsed = true;
  public occurrencesCollapseText = this.SHOW_TEXT;
  public occurrencesTitle = "Occurrences";
  public occurrenceDisplayType = 0;

  // Context names
  public contextNames = CONTEXT_NAMES_SHORT;

  // true if we need to scroll to a specific part of the definition
  public needsScroll = false;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  // user login info
  public loginInfo: LoginInfo = LOGIN_INFO_DEFAULT;

  // stores the report on changing lemma status
  public report: ChangeReport = new ChangeReportDefault();

  // true if we should use the editor view
  public usingEditorView: boolean = true;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService,
    private login: LoginInfoService,
    private modalService: NgbModal
  ) {}

  ngOnInit(): void {
    this.updateLemma(this.route.snapshot.paramMap);


    // every time the route is updated to a new stem, change the data
    // this lets us flip straight from a token to its stem
    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        let params = this.route.snapshot.paramMap;
        this.closeAllCollapsables();
        this.updateLemma(params);
      }
    });

    // stay up to date with login info
    this.login.currentLoginInfo.subscribe(loginInfo => this.loginInfo = loginInfo);
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the lemma (plus optionally a target meaning)
  updateLemma(params: ParamMap): void {
    this.targetMeaning = params.get('meaning');
    this.report = new ChangeReportDefault();

    this.isLoading = true;
    let observation = this.backendService.getEntry(params.get('entryToken'), false);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results of requesting a lemma
  handleResults(result: Entry): void {
    this.isLoading = false;
    this.entryData = result;

    // if we have a target meaning, open long definition by default.
    if (this.targetMeaning !== null) {
      this.definitionCollapse();
      this.needsScroll = true;
    }
  }

  // as soon as page is loaded, scroll to target meaning
  ngAfterViewChecked() {
    if (this.needsScroll) {
      this.needsScroll = false;
      let target = document.getElementById(this.targetMeaning);
      if (target !== null) {
        document.getElementById(this.targetMeaning).scrollIntoView();
      }
    }
  }


  // close all collapsable items
  closeAllCollapsables(): void {
    this.lemmaCollapsed = true;
    this.lemmaCollapseText = this.MORE_TEXT;

    this.definitionCollapsed = true;
    this.definitionCollapseText = this.MORE_TEXT;
    this.definitionTitle = "Short Definition"

    this.occurrencesCollapsed = true;
    this.occurrencesCollapseText = this.SHOW_TEXT;
  }


  // if the lemma collapse button is clicked, update it
  lemmaCollapse(): void {
    if (this.lemmaCollapsed) {
      this.lemmaCollapseText = this.LESS_TEXT;
    } else {
      this.lemmaCollapseText = this.MORE_TEXT;
    }
    this.lemmaCollapsed = !this.lemmaCollapsed;
  }

  // if the definition collapse button is clicked, update it
  definitionCollapse(): void {
    if (this.definitionCollapsed) {
      this.definitionCollapseText = this.LESS_TEXT;
      this.definitionTitle = "Full Definition";
    } else {
      this.definitionCollapseText = this.MORE_TEXT;
      this.definitionTitle = "Short Definition";
    }
    this.definitionCollapsed = !this.definitionCollapsed;
  }

  // if the occurrences collapse button is clicked, update it
  occurrencesCollapse(): void {
    if (this.occurrencesCollapsed) {
      this.occurrencesCollapseText = this.HIDE_TEXT;
    } else {
      this.occurrencesCollapseText = this.SHOW_TEXT;
    }
    this.occurrencesCollapsed = !this.occurrencesCollapsed;
  }

  // given an occurrence, get the router link
  getOccurrenceLink(occurrence: string, lemma: string) {
    let route = '/text/' + occurrence;
    return [route, { 'lemma': lemma }];
  }

  // get the order of columns for listing occurrences
  getOccurrenceColumns() {
    return [0, 1, 2, 3];
  }

  getColClass(col: number) {
    let colTransform1: number[]; // small order
    let colTransform2: number[]; // large order
    if (this.occurrenceDisplayType == 0 || this.contextNames.length > 4) {
      colTransform1 = [0, 2, 1, 3];
      colTransform2 = [1, 2, 3, 4];
    } else {
      colTransform1 = [0, 2, 3, 1];
      colTransform2 = [0, 1, 2, 3];
    }
    return "order-" + colTransform1[col] + " order-md-" + colTransform2[col];
  }

  // ----------------------------
  // Editor stuff

  hasEditorPermissions(): boolean {
    return this.loginInfo.accessLevel >= 2;
  }

  useEditorView(): boolean {
    return this.hasEditorPermissions() && this.usingEditorView;
  }

  // get classes for radio button
  getResolvedRadioClass(button: boolean): string {
    if (button == this.usingEditorView) {
      return "btn btn-primary";
    } else {
      return "btn btn-outline-primary";
    }
  }

  // Set whether we are showing resolved or unresolved issues only
  setShowResolved(val: boolean): void {
    if (val != this.usingEditorView) {
      this.usingEditorView = val;
    }
  }

  // get the classes for this entry's status icon
  getStatusIconClasses(): string {
    var status = +this.entryData.status;
    return "status-icon status-icon-" + status;
  }

  // get name of status
  getStatusLabel(): string {
    var status = +this.entryData.status;
    return this.STATUSES[status];
  }

  // get name of next status
  getNextStatus(): string {
    var status = +this.entryData.status;
    if (status < this.STATUSES.length - 1) {
      return this.STATUSES[status+1];
    } else {
      return this.STATUSES[this.STATUSES.length - 1];
    }
  }

  // get name of next status
  nextStatusStepsText(): string {
    var status = +this.entryData.status;
    if (status === 0) {
      return "Before marking this as first-passed reviewed, make sure it has a short and long definition as well as the appropriate root(s), semantic group(s), and compound parts (if necessary).";
    } else if (status === 1) {
      return "Before marking this article as ready for a final pass, please proofread it to make sure that all parts of the article, including especially the long definition, are accurate and typo-free.";
    } else if (status === 2) {
      return "Before marking the article as finalized, proofread it carefully. Ideally the editor who marks it as finalized is different from the editor who marks is as awaiting a final pass.";
    } else {
      return "";
    }
  }


  // get text
  getStatusDescription(): string {
    var status = +this.entryData.status;
    if (status === 0) {
      return "This article has not been reviewed. It likely still has a Betant definition and could have minor errors.";
    } else if (status === 1) {
      return "This article is a draft. It has been looked over and edited but may need more proofreading.";
    } else if (status === 2) {
      return "This article is awaiting a final proofreading pass but is nearly done";
    } else if (status === 3) {
      return "This article is finalized.";
    } else {
      return "";
    }
  }

  // true if we should render status modal
  showStatusModal(): boolean {
    return this.hasEditorPermissions() && !this.isLoading && !this.entryData.isError();
  }

  // open modal
  openModal(content) {
    this.modalService.open(content).result.then(() => {}, () => {});
  }

  // update status
  updateStatus() {
    let observation = this.backendService.updateLemmaStatus(this.entryData.token, this.entryData.status);
    observation.subscribe(results => this.handleResolve(results));
  }

  // handle resolution of updating lemma status
  handleResolve(result: ChangeReport) {
    result = result;
    if (result.error()) {
      this.report = result;
    } else {
      this.modalService.dismissAll();
      this.updateLemma(this.route.snapshot.paramMap);
    }
  }
}
