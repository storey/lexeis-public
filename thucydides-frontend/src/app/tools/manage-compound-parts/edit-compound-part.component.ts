import { Component, Input } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { CompoundInfo } from '../../compound/compound';
import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';


@Component({
  selector: 'edit-compound-part',
  templateUrl: './edit-compound-part.component.html',
  styleUrls: [ './edit-compound-part.component.css', '../form-styles.css' ]
})

export class EditCompoundPartComponent {
  public categoryName: string = "Compound Part";

  // Result locations for edit and delete
  public EDIT_RESULT_LINK: string = "/compound/";
  public DELETE_RESULT_LINK: string = "/tools/manageCompoundParts/";

  public isLoading: boolean = true;

  public item: CompoundInfo;


  // form info
  public itemName: string = "";
  public description: string = "";

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;


  // vars for editing
  loadingEdit: boolean = false;
  editReport: ChangeReport = new ChangeReportDefault();

  // vars for deleting
  loadingDelete: boolean = false;
  deleteReport: ChangeReport = new ChangeReportDefault();


  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService,
    private modalService: NgbModal
  ) {}

  ngOnInit(): void {
    this.updateInfo(this.route.snapshot.paramMap);

    // every time the route is updated to a new stem, change the data
    // this lets us flip straight from a token to its stem
    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        let params = this.route.snapshot.paramMap;
        this.updateInfo(params);
      }
    });
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the compound part
  updateInfo(params: ParamMap): void {
    this.isLoading = true;
    let observation = this.backendService.getCompoundInfo(params);
    observation.subscribe(results => this.handleResults(results));
  }


  handleResults(res: CompoundInfo) {
    this.item = res;
    this.isLoading = false;

    this.itemName = this.item.name;
    this.description = this.item.description;
  }

  // submit form
  onSubmit(form: any): void {
    let controls = form.form.controls;
    // if form is invalid, prevent submission and show error messages.
    if (!form.valid) {
      let els = [
        controls.name,
        controls.description,
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

      formData.append("item", this.item.name);

      if (this.itemName !== this.item.name) {
        formData.append("newItem", this.itemName);
        changeMade = true;
      }
      if (this.description !== this.item.description) {
        formData.append("description", this.description);
        changeMade = true;
      }

      if (changeMade) {
        let observation = this.backendService.editCompound(formData);
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

    // if edit was sucessful, show compound
    if (this.editReport.isSuccess()) {
      this.router.navigate([this.EDIT_RESULT_LINK + this.itemName]);
    }

  }

  // when delete button is clicked;
  clickDelete(modal: any) {
    this.modalService.open(modal).result.then((result) => {
      if (result) {
        this.loadingDelete = true;
        let observation = this.backendService.deleteCompound(this.item.index, this.item.name);
        observation.subscribe(results => this.handleDeleteResolve(results));
      }
    });
  }

  // Handle results of submission
  handleDeleteResolve(results: ChangeReport) {
    this.loadingDelete = false;
    this.deleteReport = results;

    if (this.deleteReport.isSuccess()) {
      this.router.navigate([this.DELETE_RESULT_LINK]);
    }
  }

}
