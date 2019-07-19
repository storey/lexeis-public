import { Component, Input } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { UnwrittenArticleList } from './unwritten-articles';
import { BackendService } from '../../backend.service';

import { boolToEnglish } from '../../globals';

@Component({
  selector: 'assigned-articles',
  templateUrl: './assigned-articles.component.html',
  styleUrls: [ './assigned-articles.component.css' ]
})

export class AssignedArticlesComponent{
  // what is the base url for this list
  public BASE_PATH: string = "/tools/assignedArticles/";

  // is this loading?
  public isLoading = true;

  // information for the lemma
  public articles: UnwrittenArticleList;

  public page = -1;


  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService
  ) {}

  ngOnInit(): void {
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

  // update the lemma
  updateArticles(params: ParamMap): void {
    this.page = +params.get('page');
    this.isLoading = true;
    let observation = this.backendService.getAssignedArticles(this.page);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: UnwrittenArticleList): void {
    this.isLoading = false;
    this.articles = results;
  }

  // Convert boolean to human-readable text
  boolToEnglish(b: boolean): string {
    return boolToEnglish(b);
  }
}
