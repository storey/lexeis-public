import { Component, NgZone } from '@angular/core';

import { ProgressUpdate, ProgressUpdateDefault } from '../change-report';
import { BackendService } from '../../backend.service';
import { BACKEND_URL } from 'src/app/lexicon-info';

@Component({
  selector: 'export',
  templateUrl: './export.component.html',
  styleUrls: [ './export.component.css' ]
})

export class ExportComponent {
  public loadingResult: boolean = false;

  // most recent update from the server
  public report: ProgressUpdate = new ProgressUpdateDefault();

  // % progress
  public progress: number = 0;

  public loadingMessage: string = "Loading...";

  constructor(
    private backendService: BackendService,
    private zone: NgZone,
  ) {}

  generateExport() : void {
    this.report = new ProgressUpdateDefault();
    this.loadingResult = true;
    this.progress = 0;
    this.loadingMessage = "Loading...";

    let observation = this.backendService.generateExport();
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results
  handleResults(results: ProgressUpdate) {
    this.zone.run(() => {
      let data = results;
      this.progress = +data.progress;
      this.loadingMessage = data.message;
      if (data.complete) {
        this.loadingResult = false;
        this.report = data;
      }
    });
  }

  // get href for exporting lexicon
  getLexiconExportHref() {
    return BACKEND_URL + "downloadLexiconExport.php";
  }
}
