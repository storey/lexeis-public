import { Component } from '@angular/core';
import { AUTHOR_NAME } from '../lexicon-info';

@Component({
  selector: 'main',
  templateUrl: './main.component.html',
  styleUrls: [ './main.component.css' ]
})

export class MainComponent{
  public author = AUTHOR_NAME;
}
