import { Component, Input } from '@angular/core';

import { Router, NavigationEnd } from '@angular/router';

import { InconsistenciesList } from './inconsistencies-list';
import { BackendService } from '../../backend.service';

@Component({
  selector: 'inconsistencies-list',
  templateUrl: './inconsistencies-list.component.html',
  styleUrls: [ './inconsistencies-list.component.css' ]
})

export class InconsistenciesListComponent{
  // type on inconsistencies to get
  @Input() incType: string;

  // is this loading?
  public isLoading = true;

  // information for the lemma
  public incs: InconsistenciesList;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;


  constructor(
    private router: Router,
    private backendService: BackendService
  ) {}

  ngOnInit(): void {
    this.updateChanges(this.incType);


    // every time the route is updated to a new stem, change the data
    // this lets us flip straight from a token to its stem
    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        this.updateChanges(this.incType);
      }
    });
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the lemma
  updateChanges(incType: string): void {
    this.isLoading = true;
    let observation = this.backendService.getInconsistencies(incType);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: InconsistenciesList): void {
    this.isLoading = false;
    this.incs = results;
  }

  // get an entry router link from a lemma
  getEntryLink(lemma: string): string {
    return "/entry/" + lemma;
  }

}
