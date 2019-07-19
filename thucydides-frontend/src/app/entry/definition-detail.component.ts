import { Component, Input } from '@angular/core';

import { Entry } from './entry';

@Component({
  selector: 'definition-detail',
  templateUrl: './definition-detail.component.html',
  styleUrls: [ './definition-detail.component.css' ]
})

export class DefinitionDetailComponent{
  @Input() myEntry: Entry;
  @Input() targetMeaning: string;

  semanticGroupsTitle(): string {
    if (this.myEntry.semanticGroups.length > 1) {
      return "Semantic Groups";
    }
    return "Semantic Group";
  }
}
