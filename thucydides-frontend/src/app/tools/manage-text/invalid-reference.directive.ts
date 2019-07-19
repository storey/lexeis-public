// Make sure the provided reference is valid
import { Directive, Input } from '@angular/core';
import { Validator, AbstractControl, NG_VALIDATORS } from '@angular/forms';

@Directive({
  selector: '[appInvalidReference]',
  providers: [{provide: NG_VALIDATORS, useExisting: InvalidReferenceDirective, multi: true}]
})
export class InvalidReferenceDirective implements Validator {
  @Input('appInvalidReference') validReferences: string[];

  validate(control: AbstractControl): {[key: string]: any} | null {
    let userValue = control.value;

    let invalid = true;
    for (let i = 0; i < this.validReferences.length; i++) {
      if (userValue === this.validReferences[i]) {
        invalid = false;
      }
    }

    if (invalid) {
      return {
        'invalidReference': {value: control.value}
      }
    }
    return null;
  }
}
