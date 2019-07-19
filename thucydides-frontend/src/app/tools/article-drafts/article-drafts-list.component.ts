import { Component, Input } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { ArticleList } from './article-drafts';
import { BackendService } from '../../backend.service';

@Component({
  selector: 'article-drafts-list',
  templateUrl: './article-drafts-list.component.html',
  styleUrls: [ './article-drafts-list.component.css' ]
})

export class ArticleDraftsListComponent{
  // is this showing user articles or submitter articles?
  @Input() userArticles: boolean;

  // what is the base url for this list
  @Input() BASE_PATH: string;

  // is this loading?
  public isLoading = true;

  // information for the lemma
  public articles: ArticleList;

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
    let observation = this.backendService.getArticleDrafts(this.page, this.userArticles);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: ArticleList): void {
    this.isLoading = false;
    this.articles = results;
  }
}
