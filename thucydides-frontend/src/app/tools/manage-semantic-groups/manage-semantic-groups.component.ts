import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { SemanticGroupList } from './semantic-group-list';
import { BackendService } from '../../backend.service';

@Component({
  selector: 'manage-semantic-groups',
  templateUrl: './manage-semantic-groups.component.html',
  styleUrls: [ './manage-semantic-groups.component.css' ]
})

export class ManageSemanticGroupsComponent {
  // is this loading?
  public isLoading = true;

  // information for the lemma
  public groups: SemanticGroupList;

  public page = -1;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  // Used in HTML
  public BASE_PATH = "/tools/manageSemanticGroups/";

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
    let observation = this.backendService.getSemanticGroupsList(this.page);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: SemanticGroupList): void {
    this.isLoading = false;
    this.groups = results;
  }
}
