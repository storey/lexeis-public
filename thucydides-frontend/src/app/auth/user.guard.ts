import { GenericGuard } from "./generic.guard";
import { Injectable } from '@angular/core';
import { LoginInfoService } from '../login-info.service';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class UserGuard extends GenericGuard {
  constructor(private l: LoginInfoService, private r: Router) {
    super(l, r);
  }

  getAccessLevel(): number {
    return 0;
  }
}
