import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';
import { MeaningInfo } from './meaning-info';
import { ChangeReport, ChangeReportDefault } from '../change-report';
import { BackendService } from '../../backend.service';

@Component({
  selector: 'edit-lemma-meanings',
  templateUrl: './edit-lemma-meanings.component.html',
  styleUrls: [ './edit-lemma-meanings.component.css', '../form-styles.css' ]
})

export class LemmaMeaningsComponent {
  public isLoading: boolean = true;
  public info: MeaningInfo;

  // Stores lemma meanings in the form
  public lemmaMeanings: string[] = [];

  // List of valid references based on this definition
  public validReferences: string[] = [];

  // List of locations with multiple instances
  public overlapList: string[] = [];


  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  // report
  loadingReport: boolean = false;
  report: ChangeReport = new ChangeReportDefault();

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService,
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

  // update the target meaning
  updateInfo(params: ParamMap): void {
    this.isLoading = true;
    let observation = this.backendService.getLemmaMeanings(params.get("lemma"));
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from lemma callback
  handleResults(results: MeaningInfo): void {
    this.isLoading = false;
    this.info = results;

    if (this.info.hasLongDefinition) {
      this.validReferences = this.getValidReferences(this.info.fullDefinition);
    }

    // initialize lemma meanings
    this.lemmaMeanings = [];
    for (let i = 0; i < this.info.occurrences.length; i++) {
      this.lemmaMeanings.push(this.info.occurrences[i][1]);
    }

    // initialize list of sections that appear multiple times
    let locationCounts = {};
    for (let i = 0; i < this.info.occurrences.length; i++) {
      let location = this.info.occurrences[i][0].split(" (")[0];
      if (location in locationCounts) {
        locationCounts[location] = locationCounts[location] + 1;
      } else {
        locationCounts[location] = 1;
      }
    }
    this.overlapList = [];
    for (let key in locationCounts){
      if (locationCounts[key] > 1) {
          this.overlapList.push(key);
      }
    }
  }

  // Get overlap warning string
  getOverlapWarning(): string {
    if (this.overlapList.length > 5) {
      let locs = this.overlapList.slice(0, 3).join(", ");
      return locs + " and " + (this.overlapList.length - 3) + " others";
    } else {
      return this.overlapList.join(", ");
    }
  }

  // Update valid references based on the long definition provided
  getValidReferences(fullDefs: any): string[] {
    // References go to the first definition by default
    return this.getIdentifiersRecursive(fullDefs[0]);
  }

  // Recursively extract identifiers from a long definition
  getIdentifiersRecursive(def: any) {
    let res = [def.text.identifier];
    for (let i = 0; i < def.subList.length; i++) {
      res = res.concat(this.getIdentifiersRecursive(def.subList[i]));
    }
    return res;
  }

  // Load meaning from the definition into the occurrence slots
  loadDefInfo() {
    if (this.info.hasLongDefinition) {
      let def = this.info.fullDefinition[0];
      let refs = this.getRefsRecursive(def);

      for (let j = 0; j < this.info.occurrences.length; j++) {
        this.lemmaMeanings[j] = "";
      }

      for (let i = 0; i < refs.length; i++) {
        let refLoc = refs[i][0];
        for (let j = 0; j < this.info.occurrences.length; j++) {
          let inputLoc = this.info.occurrences[j][0];

          // If locations match, set lemma meaning to match one from long def
          if (refLoc === inputLoc) {
            this.lemmaMeanings[j] = refs[i][1];
          }
        }
      }
    }
  }

  // Recursively get the references and associated identifiers
  getRefsRecursive(def:any): string[] {
    let res = [];

    // Get list of references associated with this identifier
    let identifier = def.text.identifier;
    for (let i = 0; i < def.text.refList.length; i++) {
      let ref = def.text.refList[i].ref;
      res.push([ref, identifier]);
    }
    for (let i = 0; i < def.text.keyPassageList.length; i++) {
      let ref = def.text.keyPassageList[i].ref;
      res.push([ref, identifier]);
    }

    // Get children
    for (let i = 0; i < def.subList.length; i++) {
      res = res.concat(this.getRefsRecursive(def.subList[i]));
    }
    return res;
  }

  // submit form
  onSubmit(form: any): void {
    let controls = form.form.controls;
    // if form is invalid, prevent submission and show error messages.
    if (!form.valid) {
      let els = [];
      for (let i = 0; i < this.info.occurrences.length; i++) {
        els.push(controls["lemmaMeaning" + i]);
      }
      for (let i = 0; i < els.length; i++) {
        if (els[i].pristine) {
          els[i].markAsTouched();
        }
      }
    } else {
      this.loadingReport = true;

      let formData = new FormData();

      formData.append("lemma", this.info.token);

      let changes = [];

      for (let i = 0; i < this.info.occurrences.length; i++) {
        let occ = this.info.occurrences[i];
        if (occ[1] != this.lemmaMeanings[i]) {
          let section = occ[0];
          let tokenID = occ[2];
          let newMeaning = this.lemmaMeanings[i]
          changes.push([section, newMeaning, tokenID]);
        }
      }

      if (changes.length > 0) {
        formData.append("changes", JSON.stringify(changes));

        let observation = this.backendService.editLemmaMeanings(formData);
        observation.subscribe(results => this.handleReportResolve(results));
      } else {
        this.loadingReport = false;
      }
    }
  }

  // Handle results of submission
  handleReportResolve(results: ChangeReport) {
    this.loadingReport = false;
    this.report = results;

    // if edit was sucessful, show lemma
    if (this.report.isSuccess()) {
      this.router.navigate(["/tools/manageText/"]);
    }
  }

}
