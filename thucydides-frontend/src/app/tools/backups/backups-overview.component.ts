import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { ChangeReport, ChangeReportDefault } from '../change-report';

import { BackupList, BackupFile } from './backup-info';
import { BackendService } from '../../backend.service';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'backups-overview',
  templateUrl: './backups-overview.component.html',
  styleUrls: [ './backups-overview.component.css' ]
})

export class BackupsOverviewComponent {
  // True if we are loading response for a new backup
  public loadingBackupResult: boolean = false;
  // Report for adding a new backup
  public backupReport: ChangeReport = new ChangeReportDefault();

  // Page of backups we are on
  public page = -1;

  public loadingBackups: boolean = true;
  public backups: BackupList;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;

  // Base path, used in html file
  public BASE_PATH = "/tools/backupsOverview/";


  // Current target backup
  private targetBackup: BackupFile;

  // True if we are loading response for a restoration
  public loadingRestoreResult: boolean = false;
  // Report for adding a new backup
  public restoreReport: ChangeReport = new ChangeReportDefault();

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService,
    private modalService: NgbModal
  ) {}

  ngOnInit(): void {
    this.updatePage(this.route.snapshot.paramMap);

    // every time the route is updated to a new stem, change the data
    // this lets us flip straight from a token to its stem
    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        let params = this.route.snapshot.paramMap;
        this.updatePage(params);
      }
    });
  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the list
  updatePage(params: ParamMap): void {
    this.page = +params.get('page');
    this.loadingBackups = true;
    let observation = this.backendService.getBackupsList(this.page);
    observation.subscribe(results => this.handleResults(results));
  }

  // handle results from the callback
  handleResults(results: BackupList): void {
    this.loadingBackups = false;
    this.backups = results;
  }

  // create a new backup
  newBackup(): void {
    this.backupReport = new ChangeReportDefault();
    this.loadingBackupResult = true;

    let observation = this.backendService.makeNewBackup();
    observation.subscribe(results => this.handleBackupResults(results));
  }

  // handle response for a backup
  handleBackupResults(result: ChangeReport) {
    this.loadingBackupResult = false;
    this.backupReport = result;

    // Update page info
    this.updatePage(this.route.snapshot.paramMap);
  }

  // Open modal on restoring a backup
  confirmRestore(b: BackupFile, confirmModal: any) {
    this.targetBackup = b;
    this.restoreReport = new ChangeReportDefault();
    this.modalService.open(confirmModal).result.then((result) => {
      if (result) {
        this.router.navigate(["/tools/recompileText/"]);
      }
    });
  }

  // Try restoring a backup
  tryRestore() {
    this.loadingRestoreResult = true;
    let observation = this.backendService.restoreBackup(this.targetBackup.filename);
    observation.subscribe(results => this.handleRestoreResults(results));
  }

  // handle response for a backup
  handleRestoreResults(result: ChangeReport) {
    this.loadingRestoreResult = false;
    this.restoreReport = result;

    if (this.restoreReport.isSuccess()) {
      // Update page info
      this.updatePage(this.route.snapshot.paramMap);
    }
  }
}
