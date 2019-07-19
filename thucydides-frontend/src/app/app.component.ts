import { Component, OnInit } from '@angular/core';
import {mergeMap, map, filter} from 'rxjs/operators';

import { Router, NavigationEnd, ActivatedRoute } from '@angular/router';

import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';

import { LoginInfo } from './user/login-info';
import { LoginInfoService } from "./login-info.service";
import { BackendService } from './backend.service';

import { LAYOUT, MAIN_CONTAINER_1, MAIN_CONTAINER_2, MAIN_CONTAINER_3 } from './globals';
import { TITLE, OVERWRITE_USER } from './lexicon-info'

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
  public title = TITLE;
  public siteVersion = '1.0.0';
  public dataVersion = '1.0.0';

  public layoutType = LAYOUT.STANDARD;

  public mainContainer1 = MAIN_CONTAINER_1.STANDARD;
  public mainContainer2 = MAIN_CONTAINER_2.STANDARD;
  public mainContainer3 = MAIN_CONTAINER_3.STANDARD;

  private loginInfo: LoginInfo;

  public navItems = [];
  public NAV_BASE = [
    ["search", "Search"],
    ["wordList", "Word List"],
    ["text", "Text"],
    ["about", "About"],
  ];

  closeResult: string;

  constructor(
    private router: Router,
    private activatedRoute: ActivatedRoute,
    private login: LoginInfoService,
    private backendService: BackendService,
    private modalService: NgbModal
  ) {}

  ngOnInit(): void {
    /* adapted from https://toddmotto.com/dynamic-page-titles-angular-2-router-events */
    this.router.events.pipe(
      filter((event) => event instanceof NavigationEnd),
      map(() => this.activatedRoute),
      map((route) => {
        while (route.firstChild) route = route.firstChild;
        return route;
      }),
      filter((route) => route.outlet === 'primary'),
      mergeMap((route) => route.data),)
      .subscribe((event) => this.updateAll(event.bodyLayout));

    // update login info
    this.updateNavItems();
    // stay up to date with login info
    this.login.currentLoginInfo.subscribe(loginInfo => this.loginInfo = loginInfo);
  }

  // update layout and nav
  updateAll(newLayout: string) {
    this.updateLayout(newLayout);
    // this.updateNavItems();
  }

  // update the layout of the page
  updateLayout(newLayout: string): void {
    this.layoutType = newLayout;
    this.mainContainer1 = MAIN_CONTAINER_1[this.layoutType];
    this.mainContainer2 = MAIN_CONTAINER_2[this.layoutType];
    this.mainContainer3 = MAIN_CONTAINER_3[this.layoutType];
  }

  // get list of nav items
  updateNavItems(): void {
    this.navItems = this.NAV_BASE;

    let observation = this.backendService.getLogin();
    observation.subscribe(results => this.handleResults(results));
  }

  handleResults(results: LoginInfo) {
    if (OVERWRITE_USER) {
      results = {
        loggedIn: true,
        firstName: "Grant",
        id: 1,
        accessLevel: 3,
      }
      // console.log(results);
    }

    this.login.changeLoginInfo(results);

    if (this.loginInfo.loggedIn) {
      this.navItems = this.NAV_BASE.concat([
        ["user", this.loginInfo.firstName]
      ])
    } else {
      this.navItems = this.NAV_BASE
    }
  }

  // open modal
  open(content) {
    this.modalService.open(content).result.then((result) => {
      this.closeResult = `Closed with: ${result}`;
    }, (reason) => {
      this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
    });
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return  `with: ${reason}`;
    }
  }
}
