import { Component } from '@angular/core';

import { LEAD_EMAIL } from '../lexicon-info';

@Component({
  selector: 'four-oh-four',
  templateUrl: './four-oh-four.component.html',
  styleUrls: [ './four-oh-four.component.css' ]
})

export class FourOhFourComponent{
  public lead_email = LEAD_EMAIL;
}
