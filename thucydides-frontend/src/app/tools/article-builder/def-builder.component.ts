import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { ChangeReport, ChangeReportDefault } from '../change-report';
import { ArticleInfo } from './article-info';
import { LoginInfoService } from "../../login-info.service";
import { LoginInfo, LOGIN_INFO_DEFAULT } from '../../user/login-info';

import { BackendService } from '../../backend.service';

import { CONTEXT_TYPES, BACKEND_URL } from "../../lexicon-info";

@Component({
  selector: 'def-builder',
  templateUrl: './def-builder.component.html',
  styleUrls: [ './def-builder.component.css' ]
})

export class DefBuilderComponent{
  // is this loading?
  public isLoading = true;

  // information for the lemma
  public lemmaData: ArticleInfo;

  public lemma: string = "";

  // ordering instances
  private instanceOrders = [
    [0, "By Location"],
    [1, "By Context"],
    [2, "By Previous Word"],
    [3, "By Next Word"]
  ];
  private currentInstOrder = this.instanceOrders[0];

  // Custom author information
  public showCustomAuthor: boolean = false;
  public customAuthor: string = "";

  // handle showing/hiding the article example
  public showArticleExample = false;
  public articleExampleShowHideText = "show";

  public submitResult: ChangeReport = new ChangeReportDefault();

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  // login information
  public loginInfo: LoginInfo = LOGIN_INFO_DEFAULT;


  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService,
    private modalService: NgbModal,
    private login: LoginInfoService,
  ) {}

  ngOnInit(): void {
    this.updateLemma(this.route.snapshot.paramMap);


    // every time the route is updated to a new stem, change the data
    // this lets us flip straight from a token to its stem
    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        let params = this.route.snapshot.paramMap;
        this.updateLemma(params);
      }
    });

    // stay up to date with login info
    this.login.currentLoginInfo.subscribe(loginInfo => this.loginInfo = loginInfo);
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the lemma
  updateLemma(params: ParamMap): void {
    this.customAuthor = "";
    this.lemma = params.get('lemma');
    this.isLoading = true;
    let observation = this.backendService.getArticleInfo(params);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results for lemma loading
  handleResults(results: ArticleInfo): void {
    this.isLoading = false;
    this.lemmaData = results;
  }

  // convert from context integer to a string
  contextTypeToName(ctString: string): string {
    var ct = +ctString;
    if (ct in CONTEXT_TYPES) {
      return CONTEXT_TYPES[ct];
    } else {
      return "";
    }
  }

  // get classes for displaying this button
  getInstOrderClass(oIndex) {
    // show the item is selected if it is selected
    if (oIndex == this.currentInstOrder[0]) {
      return "btn-primary";
    } else {
      return "btn-outline-primary";
    }
  }

  // set the instance order type
  setInstOrderType(oIndex) {
    this.currentInstOrder = this.instanceOrders[oIndex];
  }

  // Show or hide the custom author field
  toggleCustomAuthor(show: boolean) {
    this.showCustomAuthor = show;
  }

  // Show or hide the article example
  toggleArticleExample(): void {
    this.showArticleExample = !this.showArticleExample;
    if (this.showArticleExample) {
      this.articleExampleShowHideText = "hide";
    } else {
      this.articleExampleShowHideText = "show";
    }
  }

  getPriorAuthor(): string {
    if (this.lemmaData.priorCustomAuthor !== "") {
      return this.lemmaData.priorCustomAuthor;
    }
    return this.lemmaData.priorAuthor;
  }

  // submit article for review
  submitArticle(authorModal: any, raw: string, obj: any): void {
    // if there was a previous article, ask if the author wants to make this their article.
    let customAuthor = "";
    if (this.showCustomAuthor) {
      customAuthor = this.customAuthor;
    }
    let hadCustomAuthor = this.lemmaData.priorCustomAuthor !== "";
    let sameAuthor = (this.loginInfo.id === this.lemmaData.priorAuthorID && !hadCustomAuthor) || (this.lemmaData.priorCustomAuthor == customAuthor && hadCustomAuthor);
    if (this.lemmaData.priorArticle && !sameAuthor && customAuthor == "") {
      this.modalService.open(authorModal).result.then((result) => {
        if (result && hadCustomAuthor) {
          this.submitArticleToServer(this.lemmaData.priorCustomAuthor, result, raw, obj);
        } else {
          this.submitArticleToServer(customAuthor, result, raw, obj);
        }
      }, () => {});
    } else {
      this.submitArticleToServer(customAuthor, false, raw, obj);
    }
  }

  // actually submit the article
  submitArticleToServer(customAuthor: string, keepAuthor: boolean, raw: string, obj: any): void {
    let parsed = JSON.stringify(obj);
    let id = this.lemmaData.id;
    let observation = this.backendService.submitArticle(keepAuthor, this.lemmaData.priorAuthorID, this.lemmaData.oldLongDefinition, raw, parsed, id, 0, customAuthor);
    observation.subscribe(results => this.handleSubmitArticleResults(results));
  }

  handleSubmitArticleResults(result: ChangeReport) {
    this.submitResult = result;
  }

  // download the article file
  downloadFile(text) {
    var fname = this.lemma + ".txt";
    var body = text;
    this.saveFile(fname, body);
  }

  // save a file with the given data
  saveFile(filename, data) {
    var blob = new Blob([data], {type: 'text/txt'});
    if (window.navigator.msSaveOrOpenBlob) {
      window.navigator.msSaveBlob(blob, filename);
    }
    else{
      var elem = window.document.createElement('a');
      let href = window.URL.createObjectURL(blob);
      elem.href = href;
      elem.download = filename;
      document.body.appendChild(elem);
      elem.click();
      document.body.removeChild(elem);
      window.URL.revokeObjectURL(href);
    }
  }

  // get href for exporting a csv of occurrence info
  getCSVExportHref() {
    return BACKEND_URL + "occurrenceCSV.php?lemma=" + this.lemmaData.token;
  }
}
