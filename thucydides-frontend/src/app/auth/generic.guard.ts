import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { Observable } from 'rxjs';

import { LoginInfoService } from "../login-info.service";

@Injectable({
  providedIn: 'root'
})
export abstract class GenericGuard implements CanActivate {
  constructor(private login: LoginInfoService, private router: Router) {}

  ngOnInit(): void {
    // stay up to date with login info
  }

  abstract getAccessLevel(): number;

  canActivate(next: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    let url: string = state.url;

    return this.checkLogin(url);
  }

  checkLogin(url: string): boolean {
    if (this.login.isLoggedIn) {
      if (this.login.access >= this.getAccessLevel()) {
        return true;
      }

      // access denied
      this.router.navigate(['/accessDenied']);
      return false;
    }

    // Store the attempted URL for redirecting
    this.login.redirectUrl = url;

    // Navigate to the login page
    this.router.navigate(['/login']);
    return false;
  }
}
