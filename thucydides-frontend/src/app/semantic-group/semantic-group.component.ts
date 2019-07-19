import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { SemanticGroup } from './semantic-group';
import { LoginInfoService } from "../login-info.service";
import { LoginInfo, LOGIN_INFO_DEFAULT } from '../user/login-info';
import { BackendService } from '../backend.service';

@Component({
  selector: 'semanticGroup',
  templateUrl: '../root-group/group-page.component.html',
  styleUrls: [ '../root-group/group-page.component.css' ]
})

export class SemanticGroupComponent{
  public myGroup: SemanticGroup;
  public isLoading: boolean = true;
  public listTitle = "Entries of This Semantic Group:";

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  // user login info
  public loginInfo: LoginInfo = LOGIN_INFO_DEFAULT;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService,
    private login: LoginInfoService,
  ) {}

  ngOnInit(): void {
    // load the appropriate item the first time
    this.update(this.route.snapshot.paramMap);

    // every time the route is updated to a new stem, change the data
    // this lets us flip straight from a token to its stem

    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        // load the appropriate item the first time
        this.update(this.route.snapshot.paramMap);
      }
    });

    // stay up to date with login info
    this.login.currentLoginInfo.subscribe(loginInfo => this.loginInfo = loginInfo);
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // send the network request and update the shown item
  update(params: ParamMap) {
    this.isLoading = true;
    this.backendService.getSemanticGroup(params).subscribe(results => {
      this.myGroup = results;
      this.isLoading = false;
    });
  }

  // True if user has editor permissions
  hasEditorPermissions(): boolean {
    return this.loginInfo.accessLevel >= 2;
  }
}
