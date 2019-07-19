import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { Token, TokenError } from '../../text/section';
import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';

import { CONTEXT_ARRAY } from "../../lexicon-info";

@Component({
  selector: 'edit-token',
  templateUrl: './edit-token.component.html',
  styleUrls: [ './edit-token.component.css', '../form-styles.css' ]
})

export class EditTokenComponent {
  public token: Token = new TokenError();

  public loading: boolean = false;

  public contexts = CONTEXT_ARRAY;

  // Form variables
  public lemma: string;
  public lemmaMeaning: string;
  public context: number;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  // vars for report
  loadingReport: boolean = false;
  report: ChangeReport = new ChangeReportDefault();


  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService,
  ) {}

  ngOnInit(): void {
    // get initial parameters on first load
    this.updateLocation(this.route.snapshot.paramMap);

    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        this.updateLocation(this.route.snapshot.paramMap);
      }
    });

  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the current text location
  updateLocation(params: ParamMap): void {
    let tokenIndex = +params.get('tokenIndex');

    // get preprocessed text
    this.loading = true;
    let observation = this.backendService.getToken(tokenIndex);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results
  handleResults(results: Token) {
    this.loading = false;
    this.token = results;

    this.lemma = this.token.lemma;
    this.lemmaMeaning = this.token.lemmaMeaning;
    this.context = this.token.context;
  }

  // submit form
  onSubmit(form: any): void {
    let controls = form.form.controls;
    // if form is invalid, prevent submission and show error messages.
    if (!form.valid) {
      let els = [
        controls.lemma,
        controls.lemmaMeaning,
        controls.context,
      ];
      for (let i = 0; i < els.length; i++) {
        if (els[i].pristine) {
          els[i].markAsTouched();
        }
      }
    } else {
      this.loadingReport = true;

      let formData = new FormData();
      let changeMade = false;


      formData.append("tokenIndex", "" + this.token.tokenIndex);
      formData.append("lemma", this.token.lemma);
      formData.append("oldMeaning", this.token.lemmaMeaning);

      if (this.lemma !== this.token.lemma) {
        formData.append("newLemma", this.lemma);
        changeMade = true;
      }
      if (this.lemmaMeaning !== this.token.lemmaMeaning) {
        formData.append("lemmaMeaning", this.lemmaMeaning);
        changeMade = true;
      }
      if (this.context !== this.token.context) {
        formData.append("context", "" + this.context);
        changeMade = true;
      }

      if (changeMade) {
        let observation = this.backendService.editToken(formData);
        observation.subscribe(results => this.handleEditResolve(results));
      } else {
        this.loadingReport = false;
      }
    }
  }

  // Handle results of submission
  handleEditResolve(results: ChangeReport) {
    this.loadingReport = false;
    this.report = results;

    // if edit was sucessful
    if (this.report.isSuccess()) {
      this.router.navigate([this.token.getSectionEditLink()]);
    }
  }
}
