import { Component } from '@angular/core';

import { ActivatedRoute, Router, NavigationEnd, ParamMap } from '@angular/router';

import { Section, SectionError } from '../../text/section';
import { BackendService } from '../../backend.service';
import { NUM_TEXT_DIVISIONS, SMALLEST_TEXT_DIVISION } from 'src/app/lexicon-info';

@Component({
  selector: 'edit-text',
  templateUrl: './edit-text.component.html',
  styleUrls: [ './edit-text.component.css' ]
})

export class EditTextComponent {
  public section: Section = new SectionError();

  public loading: boolean = false;
  public initialLoadDone: boolean = false;

  // store the router change subscription and unsubscribe on destroy
  private routeSubscription = null;


  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private backendService: BackendService,
  ) {}

  ngOnInit(): void {
    // get initial parameters on first load
    this.updateLocation(this.route.snapshot.paramMap);

    this.routeSubscription = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        this.updateLocation(this.route.snapshot.paramMap);
      }
    });

  }

  ngOnDestroy(): void {
    this.routeSubscription.unsubscribe();
  }

  // update the current text location
  updateLocation(params: ParamMap): void {
    let location = params.get('location');

    let splt = location.split(".");

    if (splt.length !== NUM_TEXT_DIVISIONS) {
      this.loading = false;
      this.section = new SectionError();
      this.section.note = "\"" + location + "\" is not a valid " + SMALLEST_TEXT_DIVISION + ".";
    } else {
      // get preprocessed text
      this.loading = true;
      let observation = this.backendService.getSection(location);
      observation.subscribe(results => this.handleResults(results));
    }
  }

  // handle results
  handleResults(results: Section[]) {
    this.loading = false;
    this.section = results[0];
  }
}
