import { Component, ViewChild } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { Article } from './article-drafts';
import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';

import { LoginInfoService } from "../../login-info.service";
import { LoginInfo, LOGIN_INFO_DEFAULT } from '../../user/login-info';


@Component({
  selector: 'view-article-draft',
  templateUrl: './view-article-draft.component.html',
  styleUrls: [ './view-article-draft.component.css' ]
})

export class ViewArticleComponent{
  // is this loading?
  public isLoading = true;

  // information for the lemma
  public article: Article;
  public previewDef: any = null;

  public id: number = 0;

  // true if we are editing the article
  public editingArticle: boolean = false;

  public report: ChangeReport = new ChangeReportDefault();

  public loadingResolveResult: boolean = false;

  // info on user
  public loginInfo: LoginInfo = LOGIN_INFO_DEFAULT;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  @ViewChild('rejectFinished', { static: true }) rejectFinishedModal;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService,
    private modalService: NgbModal,
    private login: LoginInfoService
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

    // stay up to date with login info
    this.login.currentLoginInfo.subscribe(loginInfo => this.loginInfo = loginInfo);
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the lemma
  updateChanges(params: ParamMap): void {
    let articleID = +params.get('id');
    this.id = articleID;
    let observation = this.backendService.getArticle(articleID);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: Article): void {
    this.isLoading = false;
    this.editingArticle = false;
    this.article = results;
    if (!this.article.isError()) {
      this.previewDef = JSON.parse(this.article.longDef);
    }
  }

  // get appropriate card class based on whether issue is resolved
  getCardClass(resolved: boolean): string {
    if (resolved) {
      return "card " + "border-success";
    } else {
      return "card " + "border-danger";
    }
  }

  // Accept an article
  acceptArticle(accept: any): void {
    this.modalService.open(accept).result.then((result) => {
      if (result) {
        let observation = this.backendService.resolveArticle(this.id, true);
        observation.subscribe(results => this.handleResolve(results, false, false));
      }
    }, () => {});
  }

  // Reject an article
  rejectArticle(reject: any): void {
    this.modalService.open(reject).result.then((result) => {
      if (result) {
          let observation = this.backendService.resolveArticle(this.id, false);
          observation.subscribe(results => this.handleResolve(results, true, false));
      }
    }, () => {});
  }

  // Handle reponse to attempted reject/accept
  handleResolve(result: ChangeReport, isReject: boolean, isUpdate: boolean) {
    result = result;
    if (result.error()) {
      this.report = result;
    } else {
      // If article is updated, redirect to its successor
      if (isUpdate) {
        this.router.navigate(["/tools/articleDraft/" + result.message]);
      } else if (isReject) {
        // If reject, show an extra modal; on modal close, refresh page
        this.modalService.open(this.rejectFinishedModal).result.then(() => {
          this.report = new ChangeReportDefault();
          let params = this.route.snapshot.paramMap;
          this.updateChanges(params);
        }, () => {});
      } else {// if success, reload the page
        this.report = new ChangeReportDefault();
        let params = this.route.snapshot.paramMap;
        this.updateChanges(params);
      }
    }
  }

  // true if the article is resolved (accepted, rejected, edited)
  isResolved(): boolean {
    return this.article.status > 0;
  }

  // start editing the article
  editArticle(): void {
    this.editingArticle = true;
    let target = document.getElementById("page_title");
    if (target !== null) {
      target.scrollIntoView();
    }
  }

  // submit updated article
  updateArticle(raw: string, obj: any): void {
    // has to be put in an array for multiple articles to work
    let parsed = JSON.stringify(obj);
    let observation = this.backendService.submitArticle(false, 0, false, raw, parsed, this.article.lemmaid, this.article.id, "");
    observation.subscribe(results => this.handleResolve(results, false, true));
  }

  // does the user have editor acces
  hasEditorAccess(): boolean {
    return this.loginInfo.accessLevel >= 2;
  }
}
