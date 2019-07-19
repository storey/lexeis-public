import { Component } from '@angular/core';

import { ABOUT, HAS_SCREENCAST, SCREENCAST_LINK } from '../lexicon-info';

@Component({
  selector: 'about',
  templateUrl: './about.component.html',
  styleUrls: ['./about.component.css']
})

export class AboutComponent {
  public about_paragraph = ABOUT;
  public has_screencast = HAS_SCREENCAST;
  public screencast_link = SCREENCAST_LINK;
}
