import { Component } from '@angular/core';

import { LoginInfoService } from "../login-info.service";
import { UserPageInfo, LoginInfo, LOGIN_INFO_DEFAULT } from './login-info';
import { BackendService } from '../backend.service';

@Component({
  selector: 'user',
  templateUrl: './user.component.html',
  styleUrls: [ './user.component.css' ]
})

export class UserComponent {
  // Information about the user
  public loginInfo: LoginInfo = LOGIN_INFO_DEFAULT;

  // True if the page is loading information about incomplete articles, etc
  public isLoading: boolean = true;

  // Extra information about the page, like number of articles assigned to the user.
  public info: UserPageInfo;

  constructor(
    private backendService: BackendService,
    private login: LoginInfoService
  ) {}

  ngOnInit(): void {
    // stay up to date with login info
    this.login.currentLoginInfo.subscribe(loginInfo => this.loginInfo = loginInfo);

    //
    this.updateInfo();
  }

  // Get information for this use
  updateInfo(): void {
    this.isLoading = true;
    let observation = this.backendService.getUserPageInfo();
    observation.subscribe(results => {
      this.isLoading = false;
      this.info = results;
    });
  }

  // True if there is valid user info to display
  hasUserInfo(): boolean {
    return !this.isLoading && !this.info.error();
  }

  // Permissions functions
  hasContributorPermissions(): boolean {
    return this.loginInfo.accessLevel >= 1;
  }

  hasEditorPermissions(): boolean {
    return this.loginInfo.accessLevel >= 2;
  }

  hasAdminPermissions(): boolean {
    return this.loginInfo.accessLevel >= 3;
  }
}
