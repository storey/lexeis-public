import { Component, Input } from '@angular/core';

import { LongDef, RefItem } from './entry';

@Component({
  selector: 'full-definition-display',
  templateUrl: './full-definition.component.html',
  styleUrls: [ './full-definition.component.css' ]
})

export class FullDefinitionComponent{
  @Input() myDef: LongDef;
  @Input() lemma: string;
  @Input() targetMeaning: string;
  @Input() isMain: boolean;

  // get the router text for a reference
  getRouterLink(ref: RefItem) {
    let route = "/text/" + ref.ref;
    return [route, { 'lemma': this.lemma }];
  }

  // return whether this is the target definition
  isTargetMeaning(sublist: LongDef) {
    return (this.targetMeaning !== null) && (this.targetMeaning === sublist.text.identifier);
  }

  // return the appropriate title for the key passages section
  getKeyPassageTitle() {
    if (this.myDef.text.keyPassageList.length > 1) {
      return "Key Passages:"
    }
    return "Key Passage:"
  }


}
