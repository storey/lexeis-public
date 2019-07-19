import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { ChangeItem, ChangeList, CHANGES_PER_PAGE, ChangeLogInfo } from './change-list';
import { BackendService } from '../../backend.service';

import { ChangeReport, ChangeReportDefault } from '../change-report';

@Component({
  selector: 'changelog',
  templateUrl: './changelog.component.html',
  styleUrls: [ './changelog.component.css' ]
})

export class ChangeLogComponent{
  // base path for this list
  private BASE_PATH = "/tools/changelog/";

  // is this loading info for the page?
  public isLoadingInfo = true;

  // info for the page
  public pageInfo: ChangeLogInfo;

  // Store the filtering options
  public userID: number = -1;
  public changeTypeID: number = -1;


  // is this loading the list of changes?
  public isLoadingList = true;

  // information for the changes
  public changes: ChangeList;

  // the page of changes we are on
  public page = -1;

  // the change we may want to undo
  public undoChange: ChangeItem = new ChangeItem({});

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  // Report for undoing
  public report: ChangeReport = new ChangeReportDefault();
  public loadingUndoReport: boolean = false;


  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private modalService: NgbModal,
    private backendService: BackendService
  ) {}

  ngOnInit(): void {
    // Load page info by default
    this.loadPageInfo();

    // update list of changes for this page
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

  // load information for the page once
  loadPageInfo(): void {
    this.isLoadingInfo = true;
    let observation = this.backendService.getChangelogPageInfo();
    observation.subscribe(results => this.handlePageInfo(results));
  }


  // handling when page info is fetched
  handlePageInfo(results: ChangeLogInfo): void {
    this.isLoadingInfo = false;
    this.pageInfo = results;
    this.changeTypeID = -1;
    this.userID = -1;
  }

  // handler for user changing filter type
  filterChange(): void {
    let oldPage = this.page;

    this.router.navigate([this.BASE_PATH + "0"]);

    // if there was no change in the page, force an update
    if (oldPage === 0) {
      let params = this.route.snapshot.paramMap;
      this.updateChanges(params);
    }
  }

  // update the lemma
  updateChanges(params: ParamMap): void {
    this.page = +params.get('page');
    this.isLoadingList = true;

    //let observation = this.backendService.getUnwrittenArticles(this.page, this.show_assigned, rootFilter, semanticFilter, this.freqID);
    let observation = this.backendService.getChangelogInfo(this.page, this.userID, this.changeTypeID);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: ChangeList): void {
    this.isLoadingList = false;
    this.changes = results;
  }

  // try to undo a changes
  tryUndo(c: ChangeItem, modal:any) {
    // Reset change report
    this.report = new ChangeReportDefault();
    // Set appropriate undo change
    this.undoChange = c;
    // open modal
    this.modalService.open(modal).result.then((destination) => {
      if (destination !== "") {
        this.router.navigate([destination]);
      }
    }, () => {});
  }

  // Try to undo a change
  undoTargetChange() {
    this.loadingUndoReport = true;
    let observation = this.backendService.undoChange(this.undoChange);
    observation.subscribe(results => this.handleUndoResult(results));
  }

  // Handle change report
  handleUndoResult(result: ChangeReport) {
    this.loadingUndoReport = false;
    this.report = result;
  }

}
