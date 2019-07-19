import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { Subscription } from 'rxjs';

import { ShortEntry } from '../entry/short-entry';
import { BackendService } from '../backend.service';

import { ALPHA_UPPER, ALPHA_LOWER } from '../globals';
import { AlphaCombos, AlphaCombosDefault } from './alpha-combos';

@Component({
  selector: 'browse',
  templateUrl: './browse.component.html',
  styleUrls: [ './browse.component.css' ]
})

export class BrowseComponent {
  // valid 2-letter combos
  public loadingCombos: boolean = true;
  public combos: AlphaCombos = new AlphaCombosDefault();

  // items in the two bars
  public upperAlphabet: string[] = [];
  public secondAlphabet: string[] = [];

  // titles for the two bars
  public topLabel = "First Letter";
  public secondLabel = "First Two Letters";

  // Hold the selected letters
  public firstLetter: string = "";
  public secondLetter: string = "";

  // Whether to display the second bar of letters and show the lemma pagination
  public showSecondBar: boolean;
  public showPagination: boolean;

  public busy: Subscription;//Observable<ShortEntry>;//IBusyConfig = Object.assign({}, BUSY_CONFIG_DEFAULTS);

  // List of lemmata to display
  public matchingLemmas: ShortEntry[] = null;


  // hold current pagination page.
  public currPage: number = 1;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService
  ) {}

  ngOnInit(): void {
    this.loadAlphas();
    // Only update page after alpha results are loaded

    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        this.currPage = 1;
        this.updatePage(this.route.snapshot.paramMap);
      }
    });
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // Load the valid two-letter combos
  loadAlphas() {
    this.loadingCombos = true;
    let observation = this.backendService.getAlphaCombos();
    observation.subscribe(results => this.handleAlphaResults(results));
  }

  // Handle result of loading alpha list
  handleAlphaResults(results: AlphaCombos) {
    this.loadingCombos = false;
    this.combos = results;

    // Only update page once we have alpha results
    this.updatePage(this.route.snapshot.paramMap);
  }


  // check if list of short entries is an error
  isError(mls: ShortEntry[]): boolean {
    if ((mls.length == 0) || (mls[0].isError())) {
      return true;
    } else {
      return false
    }
  }

  // get error text associated with error
  getErrorText(mls: ShortEntry[]): string {
    if (mls.length == 0) {
      return "No entries begin with the combination " + this.firstLetter + this.secondLetter + ".";
    } else {
      return mls[0].errorMessage();
    }
  }

  // update the current location
  updatePage(params: ParamMap): void {
    this.firstLetter = params.get('firstLetter');
    this.secondLetter = params.get('secondLetter');

    this.upperAlphabet = this.mapAlphabet(ALPHA_UPPER, "", this.firstLetter);

    this.showSecondBar = (this.firstLetter !== null) && this.validFirstLetter();

    if (this.showSecondBar) {
      let combo = "";
      if (this.secondLetter !== null) {
        combo = this.firstLetter + this.secondLetter;
      }
      this.secondAlphabet = this.updateActive(this.combos.combos[this.firstLetter], combo);
    }

    this.showPagination = this.showSecondBar && (this.secondLetter !== null) && this.validSecondLetter();

    // if we are showing pagination, grab the data
    if (this.showPagination) {
      this.busy = this.backendService.getMatchingLemmas(this.firstLetter, this.secondLetter)
        .subscribe(results => this.handleResults(results));
    }
  }

  // return true if the first letter is not a valid option
  validFirstLetter() {
    return (ALPHA_UPPER.indexOf(this.firstLetter) !== -1 || (this.firstLetter == null));
  }

  // return true if the first letter is not a valid option
  validSecondLetter() {
    return (ALPHA_LOWER.indexOf(this.secondLetter) !== -1) || (this.secondLetter == null);
  }

  // handle results of loading a list of lemmata
  handleResults(results: ShortEntry[]) {
    this.matchingLemmas = results;
  }

  // given a list of items and prefix, map to items and associated router links
  mapAlphabet(alpha, prefix, activeItem) {
    let newAlpha = alpha.map(a => {
      let t = prefix + a;
      let l;
      if (prefix !== "") {
        l = '/wordList/' + prefix + "/" + a;
      } else {
        l = '/wordList/' + a;
      }
      let active = (t === activeItem);
      return {
        'text': t,
        'link': l,
        'active': active
      };
    });
    return newAlpha;
  }

  // given an alphabet, update the active item
  updateActive(alphabet, activeItem) {
    for (let i = 0; i < alphabet.length; i++) {
      if (alphabet[i].text === activeItem) {
        alphabet[i].active = true;
      } else {
        alphabet[i].active = false;
      }
    }
    return alphabet;
  }
}
