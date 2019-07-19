// This file just saves information about the user for use in different parts
// of the application.

import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

import { Router } from '@angular/router';

import { LoginInfo, LOGIN_INFO_DEFAULT } from './user/login-info';


@Injectable({
  providedIn: 'root'
})
export class LoginInfoService {
  private loginInfo = new BehaviorSubject(LOGIN_INFO_DEFAULT);
  public currentLoginInfo = this.loginInfo.asObservable();

  // store the URL so we can redirect after logging in
  public redirectUrl: string = null;

  public isLoggedIn: boolean = false;
  // Level of access
  // 0 = user (easier issue reporting)
  // 1 = contributor (can write articles)
  // 2 = editor (can edit lemmata+etc, accept articles)
  // 3 = administrator (access to backups, change log)
  public access: number = 0;

  constructor(private router: Router) {}

  // Update login info
  changeLoginInfo(info: LoginInfo) {
    this.isLoggedIn = info.loggedIn;
    this.access = info.accessLevel;
    this.loginInfo.next(info);

    if (this.redirectUrl !== null) {
      let redirect = decodeURI(this.redirectUrl);
      this.redirectUrl = null;
      this.router.navigate([redirect]);
    }
  }
}
