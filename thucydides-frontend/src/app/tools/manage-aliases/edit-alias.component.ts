import { Component, Input } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';

import { Alias } from './alias-list';
import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';


@Component({
  selector: 'edit-alias',
  templateUrl: './edit-alias.component.html',
  styleUrls: [ './edit-alias.component.css', '../form-styles.css' ]
})

export class EditAliasComponent {
  // is this loading?
  public isLoadingAlias = true;

  public alias: Alias;

  // form info
  public token: string = "";
  public lemma: string = "";

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;


  // vars for editing an alias
  loadingEdit: boolean = false;
  editReport: ChangeReport = new ChangeReportDefault();

  // vars for deleting an alias
  loadingDelete: boolean = false;
  deleteReport: ChangeReport = new ChangeReportDefault();


  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService,
    private modalService: NgbModal
  ) {}

  ngOnInit(): void {
    this.updateAlias(this.route.snapshot.paramMap);

    // every time the route is updated to a new stem, change the data
    // this lets us flip straight from a token to its stem
    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        let params = this.route.snapshot.paramMap;
        this.updateAlias(params);
      }
    });
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the target meaning
  updateAlias(params: ParamMap): void {
    this.isLoadingAlias = true;
    let observation = this.backendService.getAlias(params.get("alias"));
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: Alias): void {
    this.isLoadingAlias = false;
    this.alias = results;

    this.token = this.alias.alias;
    this.lemma = this.alias.lemma;
  }

  // submit form
  onSubmit(form: any): void {
    let controls = form.form.controls;
    // if form is invalid, prevent submission and show error messages.
    if (!form.valid) {
      let els = [
        controls.token,
        controls.lemma,
      ];
      for (let i = 0; i < els.length; i++) {
        if (els[i].pristine) {
          els[i].markAsTouched();
        }
      }
    } else {
      this.loadingEdit = true;

      let formData = new FormData();
      let changeMade = false;


      formData.append("aliasid", "" + this.alias.aliasid);

      if (this.token !== this.alias.alias) {
        formData.append("newAlias", this.token);
        changeMade = true;
      }
      if (this.lemma !== this.alias.lemma) {
        formData.append("lemma", this.lemma);
        changeMade = true;
      }

      if (changeMade) {
        let observation = this.backendService.editAlias(formData);
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
      this.router.navigate(["/tools/manageAliases"]);
    }

  }

  // when delete button is clicked;
  clickDelete(modal: any) {
    this.modalService.open(modal).result.then((result) => {
      if (result) {
        this.loadingDelete = true;
        let observation = this.backendService.deleteAlias(this.alias.alias, this.alias.aliasid);
        observation.subscribe(results => this.handleDeleteResolve(results));
      }
    });
  }

  // Handle results of submission
  handleDeleteResolve(results: ChangeReport) {
    this.loadingDelete = false;
    this.deleteReport = results;

    if (this.deleteReport.isSuccess()) {
      this.router.navigate(["/tools/manageAliases/"]);
    }
  }
}
