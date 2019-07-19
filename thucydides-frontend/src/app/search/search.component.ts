import { Component, Input } from '@angular/core';
import { Router } from "@angular/router";

import { BackendService } from '../backend.service';

import { ShortEntry } from '../entry/short-entry';

import { AUTHOR_ADJECTIVE } from '../lexicon-info';

@Component({
  selector: 'search',
  templateUrl: './search.component.html',
  styleUrls: [ './search.component.css' ]
})

export class SearchComponent{
  @Input() linkDestination: string;

  public author_adj = AUTHOR_ADJECTIVE;

  // Store prior search
  public previousSearch = "";
  public noResults = false; // true if search returned no results
  public oneAlias = false; // true if search returned a single alias
  public manyResults = false; // true if search returned multiple results
  public emptySearch = false; // true if search is empty
  public searchError = false; // true if there was an error with the search
  public results = []; // list of results for the search

  public isLoading = false; // true if the search result is loading

  // true if we should show the helper information section
  public showHelper = false;

  constructor(private router: Router,
    private backendService: BackendService) {}

  // set focus on search bar
  ngOnInit(): void {
    document.getElementById("search-bar").focus();
  }

  // search for all items that match a given search token
  searchItem(searchToken: string) {
    searchToken = searchToken.toLowerCase();
    this.noResults = false;
    this.manyResults = false;
    this.emptySearch = false;
    this.searchError = false;
    if (searchToken == "") {
      this.emptySearch = true;
    } else {
      this.previousSearch = searchToken
      this.isLoading = true;
      this.backendService.getSearchResults(searchToken)
        .subscribe(results => this.handleSearchResults(results));
    }
  }

  // given matches for a search, handle the results
  handleSearchResults(results: ShortEntry[]) {
    this.isLoading = false;
    var len = results.length;
    this.results = results;

    this.noResults = false;
    this.oneAlias = false;
    this.manyResults = false;

    if (len == 0) {
      this.noResults = true;
    } else if (len >= 1) {
      if (this.results[0].isError()) {
        this.searchError = true;
      } else if (len == 1) {
        if (!results[0].isAlias) {
          var navLocation = this.linkDestination + this.results[0].token;
          this.router.navigate([navLocation]);
        } else {
          this.oneAlias = true;
        }
      } else {
        this.manyResults = true;
      }
    }
  }

  // toggle whether to show the helper text
  toggleHelper() {
    this.showHelper = !this.showHelper;
  }

  // Get placeholder text
  getPlaceholder(): string {
    return "Any " + this.author_adj + " Word";
  }
}
