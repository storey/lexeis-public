import { Component } from '@angular/core';

import { LEAD_EMAIL } from '../lexicon-info';

@Component({
  selector: 'access-denied',
  templateUrl: './access-denied.component.html',
  styleUrls: ['./access-denied.component.css']
})

export class AccessDeniedComponent {
  public lead_email: string = LEAD_EMAIL;
  constructor() { }
}
