import { Component, Input } from '@angular/core';

@Component({
  selector: 'letter-display',
  templateUrl: './letter-display.component.html',
  styleUrls: [ './letter-display.component.css' ]
})

export class LetterDisplayComponent {
  @Input() public alphabet;
  @Input() public label: string;

  public alphabets = [];

  ngOnInit() {
    this.updateSelf()
  }

  ngOnChanges() {
    this.updateSelf()
  }

  updateSelf() {
    this.alphabets = this.splitAlphabet(this.alphabet);
  }

  // split the alphabet into sizes for various
  splitAlphabet(alpha) {
    var large = {
      'set': [alpha],
      'class': "d-none d-lg-block"
    }
    var medium = {
      'set': [alpha.slice(0, 12), alpha.slice(12)],
      'class': "d-none d-sm-block d-lg-none"
    }
    var small = {
      'set': [alpha.slice(0, 8), alpha.slice(8, 16), alpha.slice(16)],
      'class': "d-sm-none"
    }
    return [large, medium, small];
  }
}
