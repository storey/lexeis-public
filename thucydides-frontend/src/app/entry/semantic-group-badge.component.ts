import { Component, Input, OnInit } from '@angular/core';

import { SemanticPair } from './entry';

@Component({
  selector: 'semantic-group-badge',
  templateUrl: './semantic-group-badge.component.html',
  styleUrls: [ './semantic-group-badge.component.css' ]
})

export class SemanticGroupBadgeComponent{
  @Input() group: SemanticPair;
}
