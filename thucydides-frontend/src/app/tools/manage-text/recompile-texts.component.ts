import { Component, NgZone } from '@angular/core';

import { ProgressUpdate, ProgressUpdateDefault } from '../change-report';
import { BackendService } from '../../backend.service';

@Component({
  selector: 'recompile-texts',
  templateUrl: './recompile-texts.component.html',
  styleUrls: [ './recompile-texts.component.css' ]
})

export class RecompileTextsComponent {
  public loadingResult: boolean = false;

  // most recent update from the server
  public report: ProgressUpdate = new ProgressUpdateDefault();

  // % progress
  public progress: number = 0;

  constructor(
    private backendService: BackendService,
    private zone: NgZone,
  ) {}

  clickRecompile(): void {
    this.report = new ProgressUpdateDefault();
    this.loadingResult = true;
    this.progress = 0;


    let observation = this.backendService.recompileTexts();
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results
  handleResults(results: ProgressUpdate) {
    this.zone.run(() => {
      let data = results;
      this.progress = +data.progress;
      if (data.complete) {
        this.loadingResult = false;
        this.report = data;
      }
    });
  }
}
