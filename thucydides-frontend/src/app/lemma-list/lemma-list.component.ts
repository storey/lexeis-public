import { Component, Input } from '@angular/core';

import { ShortEntry } from '../entry/short-entry';

@Component({
  selector: 'lemma-list',
  templateUrl: './lemma-list.component.html',
  styleUrls: [ './lemma-list.component.css' ]
})

export class LemmaListComponent{
  @Input() entryList: ShortEntry[];
  @Input() linkDestination: string;
}
