import { Component, Input } from '@angular/core';
import { Router } from '@angular/router';

import { SECTIONS } from './sections-list';
import { TEXT_DIVISIONS, TEXT_PLACEHOLDERS, NUM_TEXT_DIVISIONS, TEXT_PART_IS_NUMBER } from '../lexicon-info';

@Component({
  selector: 'section-jump',
  templateUrl: './section-jump.component.html',
  styleUrls: [ './section-jump.component.css' ]
})

export class SectionJumpComponent{
  @Input() public displayType: string;
  @Input() public baseURL: string; // stores url to jump to

  // true if destination should have dots; false for slashes
  @Input() public useDots: boolean;
  public jumpValue: string = "";

  private JUMP_EMPTY = 0;
  private JUMP_INVALID = 1;
  private JUMP_VALID = 2;
  public jumpValid: number = this.JUMP_EMPTY;
  public invalidMessage: string = "";

  public displayTypes: string[] = TEXT_DIVISIONS;
  public placeholderExample = TEXT_PLACEHOLDERS;

  public destination: number[] = [];

  constructor(
    private router: Router
  ) {}

  // get the display type
  getDisplayType() {
    return this.displayType;
  }

  // get the placeholder
  getPlaceholder() {
    var pe = this.placeholderExample[this.displayTypes.indexOf(this.displayType)];
    return this.displayType + " (e.g " + pe + ")";
  }

  // update the jump value
  updateJump(val: string) {
    this.jumpValue = val;
    this.destination = this.validateJump();
  }

  // validate the jump value
  validateJump(): number[] {
    // handle empty case
    if (this.jumpValue == "") {
      this.jumpValid = this.JUMP_EMPTY;
      return [];
    }


    var partsRaw = this.jumpValue.split(".");


    var parts = []
    let checkLength = Math.min(partsRaw.length, this.displayTypes.length);
    for (let i = 0; i < checkLength; i++) {

      // if this is blank but the last item, we don't care
      if (partsRaw[i] == "" && i == checkLength - 1) {
        break;
      }

      let p = parseInt(partsRaw[i]);

      // check if it is not a number
      if (TEXT_PART_IS_NUMBER[i] && isNaN(p)) {
        this.jumpValid = this.JUMP_INVALID;
        this.invalidMessage = partsRaw[i] + " is not a number.";
        return [];
      } else {
        parts.push(p);
      }
    }

    // must be proper length
    if (partsRaw.length > this.displayTypes.length) {
      this.jumpValid = this.JUMP_INVALID;
      this.invalidMessage = "Saw " + partsRaw.length + " specifications (" + partsRaw.join(", ") + "), but expected at most " + this.displayTypes.length + " (" + this.displayTypes.join(", ") + ").";
      return [];
    }

    // must be a valid destination
    var track = SECTIONS;
    for (let i = 0; i < NUM_TEXT_DIVISIONS && i < partsRaw.length; i++) {
      if (!(parts[i] in track) && (parts[i] != "_last")) {
        this.jumpValid = this.JUMP_INVALID;
        this.invalidMessage = this.displayTypes[i] + " " + partsRaw.slice(0,i+1).join(".") + " does not exist.";
        return [];
      }
      if (parts.length > i+1) {
        track = track[parts[i]];
      }
    }

    this.jumpValid = this.JUMP_VALID;
    this.invalidMessage = "";
    return parts;
  }

  // return whether the jump value is invalid
  jumpInvalid() {
    return this.jumpValid == this.JUMP_INVALID;
  }

  // navigate to the specified destination
  goToDestination() {
    if (this.jumpValid == this.JUMP_VALID) {
      let myRoute = this.baseURL;
      if (this.useDots) {
        myRoute += this.destination.join(".");
      } else {
        myRoute += this.destination.join("/");
      }
      this.router.navigate([myRoute]);
    }
  }

}
