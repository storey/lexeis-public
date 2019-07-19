import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { UnwrittenArticle, UnwrittenArticleList, UnwrittenPageInfo } from './unwritten-articles';
import { BackendService } from '../../backend.service';

import { ChangeReport, ChangeReportDefault } from '../change-report';

import { boolToEnglish } from '../../globals';

@Component({
  selector: 'unwritten-articles',
  templateUrl: './unwritten-articles.component.html',
  styleUrls: [ './unwritten-articles.component.css' ]
})

export class UnwrittenArticlesComponent{
  // is this loading info for the page?
  public isLoadingInfo = true;

  // base path for this list
  public BASE_PATH = "/tools/unwrittenArticles/";

  // info for the page
  public pageInfo: UnwrittenPageInfo;

  // Store the filtering options
  public rootID: number = -1;
  public semanticID: number = -1;
  public freqID: number = -1;

  // is this loading the list of articles?
  public isLoadingList = true;

  // information for the lemma
  public articles: UnwrittenArticleList;

  public page = -1;

  // true if we are viewing assigned articles, false if we are viewing unassigned articles.
  public show_assigned = false;

  // id and name of user to assign articles to
  public assignee: number;
  public assigneeName: string = "";

  // Select all visible articles
  public allSelected: boolean = false;


  // selected articles
  public selectedArticles: number[] = [];


  // report on assigning articles
  public report: ChangeReport = new ChangeReportDefault();

  public loadingResolveResult: boolean = false;


  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private modalService: NgbModal,
    private backendService: BackendService
  ) {}

  ngOnInit(): void {
    this.loadPageInfo();

    this.updateArticles(this.route.snapshot.paramMap);

    // every time the route is updated to a new stem, change the data
    // this lets us flip straight from a token to its stem
    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        let params = this.route.snapshot.paramMap;
        this.updateArticles(params);
      }
    });
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // load information for the page once
  loadPageInfo(): void {
    this.isLoadingInfo = true;
    let observation = this.backendService.getUnwrittenPageInfo();
    observation.subscribe(results => this.handlePageInfo(results));
  }


  // handling when page info is fetched
  handlePageInfo(results: UnwrittenPageInfo): void {
    this.isLoadingInfo = false;
    this.pageInfo = results;
    if (!this.pageInfo.error()) {
      this.assignee = this.pageInfo.contributors[0].id;
      this.rootID = this.pageInfo.rootGroups[0].id;
      this.semanticID = this.pageInfo.semanticGroups[0].id;
    }
    this.freqID = -1;
  }

  // handler for user changing filter type
  refreshArticles(): void {
    this.report = new ChangeReportDefault();

    // If we are already on page 0
    if (this.page == 0) {
      // Trigger change
      this.updateArticles(this.route.snapshot.paramMap);
    } else { // Otherwise
      // Navigate to page 0, which triggers change
      this.router.navigate([this.BASE_PATH + "0"]);
    }
  }

  // update the lemma
  updateArticles(params: ParamMap): void {
    this.page = +params.get('page');
    this.isLoadingList = true;

    // get filter information
    let rootFilter = "";
    if (this.rootID !== -1 && this.rootID !== this.pageInfo.rootGroups[0].id) {
      for (let i = 0; i < this.pageInfo.rootGroups.length; i++) {
        if (this.pageInfo.rootGroups[i].id === this.rootID) {
          rootFilter = this.pageInfo.rootGroups[i].name;
        }
      }
    }
    let semanticFilter = -1;
    if (this.semanticID !== -1 && this.semanticID !== this.pageInfo.semanticGroups[0].id) {
      semanticFilter = this.semanticID;
    }

    let observation = this.backendService.getUnwrittenArticles(this.page, this.show_assigned, rootFilter, semanticFilter, this.freqID);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: UnwrittenArticleList): void {
    this.allSelected = false;
    this.isLoadingList = false;
    this.articles = results;
  }

  // get error text associated with an entry
  getErrorText(e: UnwrittenArticleList): string {
    if (+e.size === 0) {
      let note = "";
      if(this.rootID !== -1 || this.semanticID !== -1) {
          note = " matching the provided filter criteria";
      }

      if (this.show_assigned) {
        return "There are no assigned articles" + note + " that need to be completed.";
      } else {
        return "There are no unassigned articles" + note + " that need to be completed.";
      }
    } else {
      return e.message;
    }
  }

  // get classes for radio button
  getAssignedRadioClass(button: boolean): string {
    if (button == this.show_assigned) {
      return "btn btn-primary";
    } else {
      return "btn btn-outline-primary";
    }
  }

  // Set whether we are showing assigned or unassigned articles
  setShowAssigned(val: boolean): void {
    if (val != this.show_assigned) {
      this.show_assigned = val;

      this.refreshArticles();
    }
  }

  // Convert boolean to human-readable text
  getAssignedText(assigned: boolean): string {
    return boolToEnglish(assigned);
  }

  // select/deselect an article
  selectAll(): void {
    this.allSelected = !this.allSelected;
    for (let i = 0; i < this.articles.list.length; i++) {
      this.articles.list[i].checked = this.allSelected;
    }
  }

  // select/deselect an article
  selectArticle(a: UnwrittenArticle): void {
    a.checked = !a.checked;

    let allSelected = true;
    let allUnselected = true;
    for (let i = 0; i < this.articles.list.length; i++) {
      if (this.articles.list[i].checked) {
        allUnselected = false;
      } else {
        allSelected = false;
      }
    }

    if (allSelected && !allUnselected) {
      this.allSelected = true;
    } else if (!allSelected && allUnselected) {
      this.allSelected = false;
    } else { // Mix of selected and unselected
      this.allSelected = false;
    }
  }

  // submit form
  onSubmit(form: any, modal: any): void {
    // get the chosen assignee's name
    for (let i = 0; i < this.pageInfo.contributors.length; i++) {
      if (this.pageInfo.contributors[i].id == this.assignee) {
        this.assigneeName = this.pageInfo.contributors[i].name;
        break;
      }
    }

    // get selected articles
    this.selectedArticles = []
    for (let i = 0; i < this.articles.list.length; i++) {
      let a = this.articles.list[i]
      if (a.checked === true) {
        this.selectedArticles.push(a.lemmaid);
      }
    }

    // open modal
    this.modalService.open(modal).result.then((result) => {
      if (result) {
        let observation = this.backendService.assignUnwrittenArticles(this.assignee, this.selectedArticles);
        observation.subscribe(results => this.handleResolve(results));
      }
    }, () => {});

  }

  handleResolve(result: ChangeReport) {
    result = result;
    if (result.error()) {
      this.report = result;
    } else {
      this.router.navigate([this.BASE_PATH + "0"]);

      this.report = new ChangeReportDefault();
      let params = this.route.snapshot.paramMap;
      this.updateArticles(params);
    }
  }

  // true if no articles are selected
  noArticlesSelected(): boolean {
    for (let i = 0; i < this.articles.list.length; i++) {
      if (this.articles.list[i].checked === true) {
        return false;
      }
    }
    return true;
  }
}
