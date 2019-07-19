import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { RootList } from './root-list';
import { BackendService } from '../../backend.service';

@Component({
  selector: 'manage-roots',
  templateUrl: './manage-roots.component.html',
  styleUrls: [ './manage-roots.component.css' ]
})

export class ManageRootsComponent {
  // is this loading?
  public isLoading = true;

  // information for the lemma
  public roots: RootList;

  public page = -1;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  // used in HTML
  public BASE_PATH = "/tools/manageRoots/";

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService
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
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the list
  updateChanges(params: ParamMap): void {
    this.page = +params.get('page');
    this.isLoading = true;
    let observation = this.backendService.getRootsList(this.page);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: RootList): void {
    this.isLoading = false;
    this.roots = results;
  }
}
