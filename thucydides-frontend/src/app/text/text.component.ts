import { Component } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';


import { Observable } from 'rxjs';

import { PreppedText, PreppedTextError } from './prepped-text';
import { LoginInfoService } from "../login-info.service";
import { LoginInfo, LOGIN_INFO_DEFAULT } from '../user/login-info';
import { BackendService } from '../backend.service';

import { AUTHOR_NAME, AUTHOR_TEXT_TITLE, AUTHOR_TEXT_PUBLISHING_INFO, STORAGE_CONTEXT_DISPLAY_NAME,
  NUM_TEXT_DIVISIONS, TEXT_DIVISIONS, TEXT_DEFAULT_VALUES } from '../lexicon-info';

@Component({
  selector: 'text',
  templateUrl: './text.component.html',
  styleUrls: [ './text.component.css' ]
})

export class TextComponent{
  public author_name = AUTHOR_NAME;

  // stores the HTML for displaying the text
  // Default to the network error so we always shows something
  public displayText: PreppedText = new PreppedTextError();

  public displayTypes = TEXT_DIVISIONS;
  public displayType: string = "";
  public contextDisplayType: string = "";
  public locationSpec: string[] = TEXT_DEFAULT_VALUES;
  public targetLemma: string = null;

  public loading: boolean = false;
  public initialLoadDone: boolean = false;

  // hold the busy object
  public busy: Observable<PreppedText>;

  // store the number of outstanding requests
  public outstandingRequests: number = 0;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  // user login info
  public loginInfo: LoginInfo = LOGIN_INFO_DEFAULT;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService,
    private sanitizer: DomSanitizer,
    private login: LoginInfoService
  ) {}

  ngOnInit(): void {
    // get initial parameters on first load
    this.updateLocation(this.route.snapshot.paramMap);

    // get context display type
    let cdt = localStorage.getItem(STORAGE_CONTEXT_DISPLAY_NAME);
    if (cdt !== null) {
      this.contextDisplayType = cdt;
    }

    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        this.updateLocation(this.route.snapshot.paramMap);
      }
    });

    // stay up to date with login info
    this.login.currentLoginInfo.subscribe(loginInfo => this.loginInfo = loginInfo);
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the current text location
  updateLocation(params: ParamMap): void {
    let specificLocationSpec: string = params.get('locationSpec');

    let locSpec = specificLocationSpec.split(".");
    this.displayType = this.displayTypes[locSpec.length-1];


    for (let i = locSpec.length; i < NUM_TEXT_DIVISIONS; i++) {
      locSpec.push("1");
    }
    this.locationSpec = locSpec;


    this.targetLemma = params.get('lemma');

    if (!this.initialLoadDone) {
      this.loading = true;
    }

    //this.wipeText();

    let observation = null;

    // get preprocessed text
    observation = this.backendService.getPreppedText(specificLocationSpec, this.targetLemma);
    this.outstandingRequests += 1;
    this.busy = observation.subscribe(results => this.handleResults(results));
  }

  // convert compressed version of text into HTML
  decompressText(text: string): string {
    let html = text;
    html = html.replace(/@([^@]+)@([^@]+)@/g, "<a href=\"\#\/entry\/$1\">$2<\/a>")

    html = html.replace(/;([^<>]+)">/g, ";meaning=$1\">");

    // lemma highlighting
    let lemRE = new RegExp("(href=\"#/entry/" + this.targetLemma + "[;\"])", "g")
    html = html.replace(lemRE, "class=\"target-lemma\" $1");

    return html;
  }

  // handle results
  handleResults(results) {
    this.outstandingRequests -= 1;
    // TODO: way to make this less brittle?
    if (this.outstandingRequests == 0) {
      this.loading = false;
      this.initialLoadDone = true;

      this.displayText = results;

      // we only need to do this for non-cached things
      if ((typeof this.displayText["rawHTML"]) === "string") {
        this.displayText["rawHTML"] = this.sanitizer.bypassSecurityTrustHtml(this.decompressText(this.displayText["rawHTML"]));
      }
    }
  }

  // Get the title of the text this work is in
  getTextName(): string {
    return AUTHOR_TEXT_TITLE;
  }

  // Get the publishing info for the source this text is in
  getTextPubInfo(): string {
    return AUTHOR_TEXT_PUBLISHING_INFO;
  }

  // change the context display type
  changeContextDisplay(event: string) {
    this.contextDisplayType = event;
    // store preference in local storage
    localStorage.setItem(STORAGE_CONTEXT_DISPLAY_NAME, event);
  }

  // Get link for editing as ection
  getEditLink(): string {
    return "/tools/editText/" + this.locationSpec.join(".");
  }

  // True if user has editor permissions
  hasEditorPermissions(): boolean {
    return this.loginInfo.accessLevel >= 2;
  }

  // True if we should show the edit link
  showEditLink(): boolean {
    return this.displayType == this.displayTypes[NUM_TEXT_DIVISIONS-1] && this.hasEditorPermissions();
  }
}
