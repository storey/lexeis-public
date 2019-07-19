import { Component, Input } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { SemanticGroup } from '../../semantic-group/semantic-group';
import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';

import { SEMANTIC_GROUP_COLOR_OPTIONS } from '../../globals';


@Component({
  selector: 'edit-semantic-group',
  templateUrl: './edit-semantic-group.component.html',
  styleUrls: [ './edit-semantic-group.component.css', '../form-styles.css', '../../entry/semantic-group-badge.component.css' ]
})

export class EditSemanticGroupComponent {
  public categoryName: string = "Semantic Group";
  public EDIT_RESULT_LINK: string = "/semanticGroup/";
  public DELETE_RESULT_LINK: string = "/tools/manageSemanticGroups/";

  public isLoading: boolean = true;

  public item: SemanticGroup;


  // form info
  public colorOptions = SEMANTIC_GROUP_COLOR_OPTIONS;

  public itemName: string = "";
  public displayType: string = "0";
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
    let observation = this.backendService.getSemanticGroup(params);
    observation.subscribe(results => this.handleResults(results));
  }

  // Set variables when current semantic group is loaded
  handleResults(res: any) {
    this.item = res;
    this.isLoading = false;

    this.itemName = this.item.name;
    this.displayType = this.item.labelClass;
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
      if (this.displayType !== this.item.labelClass) {
        formData.append("displayType", this.displayType);
        changeMade = true;
      }
      if (this.description !== this.item.description) {
        formData.append("description", this.description);
        changeMade = true;
      }
      if (changeMade) {
        let observation = this.backendService.editSemanticGroup(formData);
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
      this.router.navigate([this.EDIT_RESULT_LINK + this.item.index]);
    }

  }

  // when delete button is clicked;
  clickDelete(modal: any) {
    this.modalService.open(modal).result.then((result) => {
      if (result) {
        this.loadingDelete = true;
        let observation = this.backendService.deleteSemanticGroup(this.item.index, this.item.name);
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
