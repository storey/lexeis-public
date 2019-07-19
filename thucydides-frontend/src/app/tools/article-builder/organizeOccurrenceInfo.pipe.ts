import { Pipe, PipeTransform } from '@angular/core';
import { NUM_TEXT_DIVISIONS } from 'src/app/lexicon-info';
/*
 * Takes a list of occurrences and sorts it by location, prev word, or next word
*/

// Compare two section strings
function sectionCompare(a, b) {
  let aSplt = a[0].split(".");
  let bSplt = b[0].split(".");
  // Compare text divisions
  for (let i = 0; i < NUM_TEXT_DIVISIONS; i++) {
    if (aSplt[i] != bSplt[i]) {
      return parseInt(aSplt[i]) - parseInt(bSplt[i]);
    }
  }
  return 0;
}

@Pipe({name: 'organizeOccurrenceInfo'})
export class OrganizeOccurrenceInfoPipe implements PipeTransform {
  transform(values: string[][], args: number[]): string[][] {
    let organizeType: number = args[0];
    if (organizeType == 0) {
      // sort by occurrence
      values.sort(sectionCompare);
      return values;
    } else if (organizeType == 1) {
      // sort by context
      values.sort(function (a, b) {
        let comp = parseInt(a[1]) - parseInt(b[1]);
        // default to location compare
        if (comp === 0) {
          return sectionCompare(a, b);
        }
        return comp;
      });
      return values;
    } else if (organizeType == 2) {
      // sort by previous
      values.sort(function (a, b) {
        let comp = a[2].localeCompare(b[2]);
        // default to location compare
        if (comp === 0) {
          return sectionCompare(a, b);
        }
        return comp;
      });
      return values;
    } else if (organizeType == 3) {
      // sort by next
      values.sort(function (a, b) {
        let comp = a[4].localeCompare(b[4]);
        // default to location compare
        if (comp === 0) {
          return sectionCompare(a, b);
        }
        return comp;
      });
      return values;
    } else {
      return values;
    }
  }
}
