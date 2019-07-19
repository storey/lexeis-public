import { Component, Input } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { Entry } from '../../entry/entry';
import { LemmaOptionsInfo } from '../info-lists';
import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';


@Component({
  selector: 'edit-lemma',
  templateUrl: './edit-lemma.component.html',
  styleUrls: [ './edit-lemma.component.css', '../form-styles.css' ]
})

export class EditLemmaComponent {
  public isLoading: boolean = true;
  public info: LemmaOptionsInfo;

  // is this loading?
  public isLoadingLemma = true;

  public lemma: Entry;

  // form info
  public token: string = "";
  public shortDef: string = "";
  public pos: string = "";
  public compoundParts: string[] = [];
  public roots: string[] = [];
  public semanticGroups: string[] = [];
  public hasIllustration: boolean = false;

  // true if we use the former illustration
  public useOldIllustration: boolean = true;
  public illustration: any;
  public illustrationFile: any = null;
  public caption: string = "";
  public bibliography: string = "";

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;


  // vars for editing a lemma
  loadingEdit: boolean = false;
  editReport: ChangeReport = new ChangeReportDefault();

  // vars for deleting a lemma
  loadingDelete: boolean = false;
  deleteReport: ChangeReport = new ChangeReportDefault();


  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService,
    private modalService: NgbModal
  ) {}

  ngOnInit(): void {
    this.setLemmaInfo();

    this.updateLemma(this.route.snapshot.paramMap);

    // every time the route is updated to a new stem, change the data
    // this lets us flip straight from a token to its stem
    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        let params = this.route.snapshot.paramMap;
        this.updateLemma(params);
      }
    });
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update illustration file when it is changed
  onIllustrationChanged(event) {
    this.illustrationFile = event.target.files[0];
  }

  // update the target meaning
  setLemmaInfo(): void {
    this.isLoading = true;
    let observation = this.backendService.getLemmaOptions();
    observation.subscribe(results => this.handleInfoResults(results));
  }

  handleInfoResults(res: LemmaOptionsInfo) {
    this.info = res;
    this.isLoading = false;
  }

  // update the target meaning
  updateLemma(params: ParamMap): void {
    this.isLoadingLemma = true;
    let observation = this.backendService.getEntry(params.get("lemma"), true);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: Entry): void {
    this.isLoadingLemma = false;
    this.lemma = results;

    this.token = this.lemma.token;
    this.shortDef = this.lemma.shortDef;
    this.pos = this.lemma.partOfSpeech;
    this.compoundParts = this.lemma.compoundParts;
    this.roots = this.lemma.stemType;
    this.hasIllustration = this.lemma.hasIllustration;
    this.caption = this.lemma.illustrationCaption;
    this.bibliography = this.lemma.bibliographyText;

    // hack because the numbers aren't immediately recognized as matching values
    let sg = [];
    if (!this.lemma.isError()){
      for (let i = 0; i < this.lemma.semanticGroups.length; i++) {
        sg.push(this.lemma.semanticGroups[i] + "")
      }
    }
    this.semanticGroups = sg;// this.lemma.semanticGroups;
  }

  // return true if these lists are different (ignoring order)
  listsDifferent(a: string[], b: string[]) {
    a = a.sort();
    b = b.sort();

    if (a.length !== b.length) {
      return true;
    }

    for (let i = 0; i < a.length; i++) {
      if (a[i] !== b[i]) {
        return true;
      }
    }
    return false;
  }

  // submit form
  onSubmit(form: any): void {
    let controls = form.form.controls;
    // if form is invalid, prevent submission and show error messages.
    if (!form.valid) {
      let els = [
        controls.lemma,
        controls.shortDef,
        controls.pos,
      ];
      if (this.hasIllustration) {
        els.push(controls.illustration);
        els.push(controls.caption);
      }
      for (let i = 0; i < els.length; i++) {
        if (els[i].pristine) {
          els[i].markAsTouched();
        }
      }
    } else {
      this.loadingEdit = true;

      let submitCaption = "";
      if (this.hasIllustration) {
        submitCaption = this.caption;
      }

      let formData = new FormData();
      let changeMade = false;


      formData.append("lemma", this.lemma.token);

      if (this.token !== this.lemma.token) {
        formData.append("newLemma", this.token);
        changeMade = true;
      }
      if (this.shortDef !== this.lemma.shortDef) {
        formData.append("shortDef", this.shortDef);
        changeMade = true;
      }
      if (this.pos !== this.lemma.partOfSpeech) {
        formData.append("pos", this.pos);
        changeMade = true;
      }


      if (this.listsDifferent(this.compoundParts, this.lemma.compoundParts)) {
        formData.append("compoundParts", JSON.stringify(this.compoundParts));
        changeMade = true;
      }
      if (this.listsDifferent(this.roots, this.lemma.stemType)) {
        formData.append("roots", JSON.stringify(this.roots));
        changeMade = true;
      }

      let sgs = [];
      for (let i = 0; i < this.lemma.semanticGroups.length; i++) {
        sgs.push("" + this.lemma.semanticGroups[i]);
      }
      if (this.listsDifferent(this.semanticGroups, sgs)) {
        formData.append("semanticGroups", JSON.stringify(this.semanticGroups));
        changeMade = true;
      }


      if (this.hasIllustration !== this.lemma.hasIllustration) {
        formData.append("hasIllustration", "" + this.hasIllustration);
        changeMade = true;
      }
      if (submitCaption !== this.lemma.illustrationCaption) {
        formData.append("caption", submitCaption);
        changeMade = true;
      }
      if (this.bibliography !== this.lemma.bibliographyText) {
        formData.append("bibliography", this.bibliography);
        changeMade = true;
      }

      // If we have an illustration, include it, unless there was an old
      // Illustration and we specify that we want to use it
      if (this.hasIllustration && !(this.useOldIllustration && this.lemma.illustrationLink !== '')) {
        formData.append("illustration", this.illustrationFile);
        changeMade = true;
      }

      if (changeMade) {
        let observation = this.backendService.editLemma(formData);
        observation.subscribe(results => this.handleEditResolve(results));
      } else {
        this.loadingEdit = false;
      }
    }
  }

  // Handle results of submission
  handleEditResolve(results: ChangeReport) {
    this.loadingEdit = false;
    this.editReport = results;

    // if edit was sucessful, show lemma
    if (this.editReport.isSuccess()) {
      this.router.navigate(["/entry/" + this.token]);
    }

  }

  // when delete button is clicked;
  clickDelete(modal: any) {
    this.modalService.open(modal).result.then((result) => {
      if (result) {
        this.loadingDelete = true;
        let observation = this.backendService.deleteLemma(this.lemma.token, this.lemma.lemmaid);
        observation.subscribe(results => this.handleDeleteResolve(results));
      }
    });
  }

  // Handle results of submission
  handleDeleteResolve(results: ChangeReport) {
    this.loadingDelete = false;
    this.deleteReport = results;

    if (this.deleteReport.isSuccess()) {
      this.router.navigate(["/tools/manageLemmata/"]);
    }
  }


  // get classes for illustration radio button
  getIllustrationRadioClass(button: boolean): string {
    if (button == this.useOldIllustration) {
      return "btn btn-primary";
    } else {
      return "btn btn-outline-primary";
    }
  }

  // Set whether we are using old illustration or a new one
  setUseOldIllustration(val: boolean): void {
    if (val != this.useOldIllustration) {
      this.useOldIllustration = val;
    }
  }

  // true if we show file picker for new illustration
  showIllustrationFilePicker(): boolean {
    let hadIllustration = this.lemma.illustrationLink !== '';
    if (hadIllustration) {
      return this.hasIllustration && !this.useOldIllustration;
    } else {
      return this.hasIllustration;
    }
  }

  // true if we show file picker for new illustration
  showOldIllustration(): boolean {
    let hadIllustration = this.lemma.illustrationLink !== '';
    if (hadIllustration) {
      return this.hasIllustration && this.useOldIllustration;
    } else {
      return false;
    }
  }

}
