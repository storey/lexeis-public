import { Component } from '@angular/core';

import { Router } from '@angular/router';

import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';

@Component({
  selector: 'add-root',
  templateUrl: './add-root.component.html',
  styleUrls: [ './add-root.component.css', '../form-styles.css' ]
})

export class AddRootComponent {
  public categoryName: string = "Root";
  public ADD_RESULT_LINK: string = "/rootGroup/";

  public isLoading: boolean = true;

  // form info
  public itemName: string = "";
  public description: string = "";

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
      formData.append("description", this.description);

      let observation = this.backendService.addRoot(formData);
      observation.subscribe(results => this.handleReportResults(results));
    }
  }

  // Handle results of submission
  handleReportResults(results: ChangeReport) {
    this.loadingReport = false;
    this.report = results;

    if (this.report.isSuccess()) {
      this.router.navigate([this.ADD_RESULT_LINK + this.itemName]);
    }
  }
}
