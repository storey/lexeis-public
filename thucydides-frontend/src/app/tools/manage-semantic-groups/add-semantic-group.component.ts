import { Component } from '@angular/core';

import { Router } from '@angular/router';

import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';

import { SEMANTIC_GROUP_COLOR_OPTIONS } from '../../globals';

@Component({
  selector: 'add-semantic-group',
  templateUrl: './add-semantic-group.component.html',
  styleUrls: [ './add-semantic-group.component.css', '../form-styles.css', '../../entry/semantic-group-badge.component.css' ]
})

export class AddSemanticGroupComponent {
  public categoryName: string = "Semantic Group";
  public ADD_RESULT_LINK: string = "/semanticGroup/";

  public isLoading: boolean = true;

  // form info
  public colorOptions = SEMANTIC_GROUP_COLOR_OPTIONS;

  // Information for semantic group
  public itemName: string = "";
  public description: string = "";
  public displayType: string = "0";

  // resulting report
  public loadingReport = false;

  public report: ChangeReport = new ChangeReportDefault();

  constructor(
    private router: Router,
    private backendService: BackendService
  ) {}

  ngOnInit(): void {
    this.resetFormValues();
  }

  // Reset the user input values
  resetFormValues():void {
    this.itemName = "";
    this.description = "";
    this.displayType = "0";
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
      this.loadingReport = true;

      let formData = new FormData();

      formData.append("item", this.itemName);
      formData.append("displayType", this.displayType);
      formData.append("description", this.description);

      let observation = this.backendService.addSemanticGroup(formData);
      observation.subscribe(results => this.handleReportResults(results));
    }
  }

  // Handle results of submission
  handleReportResults(results: ChangeReport) {
    this.loadingReport = false;
    this.report = results;

    if (this.report.isSuccess()) {
      this.router.navigate([this.ADD_RESULT_LINK + this.report.message]);
    }
  }
}
