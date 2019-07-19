import { Component } from '@angular/core';

import { Router } from '@angular/router';

import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';


@Component({
  selector: 'add-alias',
  templateUrl: './add-alias.component.html',
  styleUrls: [ './add-alias.component.css', '../form-styles.css' ]
})

export class AddAliasComponent {
  public alias: string = "";
  public lemma: string = "";

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
  resetFormValues(): void {
    this.alias = "";
    this.lemma = "";
  }

  // submit form
  onSubmit(form: any): void {
    let controls = form.form.controls;
    // if form is invalid, prevent submission and show error messages.
    if (!form.valid) {
      let els = [
        controls.alias,
        controls.lemma,
      ];
      for (let i = 0; i < els.length; i++) {
        if (els[i].pristine) {
          els[i].markAsTouched();
        }
      }
    } else {
      this.loadingReport = true;

      let formData = new FormData();

      formData.append("alias", this.alias);
      formData.append("lemma", this.lemma);

      let observation = this.backendService.addAlias(formData);
      observation.subscribe(results => this.handleReportResults(results));
    }
  }

  // Handle results of submission
  handleReportResults(results: ChangeReport) {
    this.loadingReport = false;
    this.report = results;

    if (this.report.isSuccess()) {
      this.router.navigate(["/tools/manageAliases/0"]);
    }
  }

}
