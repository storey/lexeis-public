import { Component } from '@angular/core';

import { Router } from '@angular/router';

import { LemmaOptionsInfo } from '../info-lists';

import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';


@Component({
  selector: 'add-lemma',
  templateUrl: './add-lemma.component.html',
  styleUrls: [ './add-lemma.component.css', '../form-styles.css' ]
})

export class AddLemmaComponent {
  public isLoading: boolean = true;
  public info: LemmaOptionsInfo;

  public lemma: string = "";
  public shortDef: string = "";
  public pos: string = "";
  public compoundParts: string[] = [];
  public roots: string[] = [];
  public semanticGroups: string[] = [];
  public hasIllustration: boolean = false;
  public illustration: any = null;
  public illustrationFile: any = null;
  public caption: string = "";
  public bibliography: string = "";

  public loadingReport = false;

  public report: ChangeReport = new ChangeReportDefault();

  constructor(
    private router: Router,
    private backendService: BackendService
  ) {}

  ngOnInit(): void {
    this.resetFormValues();


    this.setLemmaInfo();
  }

  // update the target meaning
  setLemmaInfo(): void {
    this.isLoading = true;
    let observation = this.backendService.getLemmaOptions();
    observation.subscribe(results => this.handleResults(results));
  }

  handleResults(res: LemmaOptionsInfo) {
    this.info = res;
    this.isLoading = false;
  }

  // Reset the user input values
  resetFormValues():void {
    this.shortDef = "";
    this.pos = "";
    this.compoundParts = [];
    this.roots = [];
    this.semanticGroups = [];
    this.hasIllustration = false;
    this.illustration = null;
    this.caption = "";
    this.bibliography = "";
  }

  // update illustration file when it is changed
  onIllustrationChanged(event) {
    this.illustrationFile = event.target.files[0];
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
      this.loadingReport = true;

      let submitCaption = "";
      if (this.hasIllustration) {
        submitCaption = this.caption;
      }

      let formData = new FormData();

      formData.append("lemma", this.lemma);
      formData.append("shortDef", this.shortDef);
      formData.append("pos", this.pos);
      formData.append("compoundParts", JSON.stringify(this.compoundParts));
      formData.append("roots", JSON.stringify(this.roots));
      formData.append("semanticGroups", JSON.stringify(this.semanticGroups));
      formData.append("hasIllustration", "" + this.hasIllustration);
      formData.append("caption", submitCaption);
      formData.append("bibliography", this.bibliography);
      if (this.hasIllustration) {
        formData.append("illustration", this.illustrationFile);
      } else {
        formData.append("illustration", "");
      }
      let observation = this.backendService.addLemma(formData);
      observation.subscribe(results => this.handleReportResults(results));
    }
  }

  // Handle results of submission
  handleReportResults(results: ChangeReport) {
    this.loadingReport = false;
    this.report = results;

    if (this.report.isSuccess()) {
      this.router.navigate(["/entry/" + this.lemma]);
    }
  }

}
