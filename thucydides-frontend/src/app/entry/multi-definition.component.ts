import { Component, Input } from '@angular/core';

import { OLD_DICTIONARY_REF } from '../lexicon-info';

@Component({
  selector: 'multi-definition',
  templateUrl: './multi-definition.component.html',
  styleUrls: [ './multi-definition.component.css' ]
})

export class MultiDefinitionComponent{
  @Input() fullDefinition: any;
  @Input() targetMeaning: string;
  @Input() oldLongDefinition: boolean;
  @Input() token: string;
  @Input() authorName: string;

  public definitionIndex: number = 0;

  public old_dict_ref = OLD_DICTIONARY_REF;

  // return whether this is the target definition
  getDefTitle(d, def) {
    if ("defType" in def && def.defType != "") {
      return def.defType;
    }
    if (def.text.identifier != "") {
      return def.text.identifier;
    }
    return "";
  }
}
