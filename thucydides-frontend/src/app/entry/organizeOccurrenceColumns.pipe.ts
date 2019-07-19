import { Pipe, PipeTransform } from '@angular/core';

import { CONTEXT_ARRAY } from '../lexicon-info';

/*
 * Takes a list of occurrences and sorts it into 4 columns, either
 * by order or by context type.
*/

// given a list of values and the organization type, combine values that
// are the same. When just showing the list in order, this means all
// references; when showing the list by context, this means all references
// of the same context.
function combineSameValues(values: string[], organizeType: number): string[][] {
  let typeLists;
  if (organizeType == 0) {
    typeLists = [{}]
  } else {
    typeLists = []
    for (let i = 0; i < CONTEXT_ARRAY.length; i++) {
      typeLists.push({});
    }
  }
  // Store counts of each location reference
  for (let val of values) {
    let tl;
    // get the list for the appropriate context type, if necessary
    if (organizeType == 0) {
      tl = typeLists[0]
    } else {
      tl = typeLists[val[1]]
    }
    if (val[0] in tl) {
      tl[val[0]] += 1;
    } else {
      tl[val[0]] = 1;
    }
  }
  // add each location, with (x2) if it appears twice, (x3) if it appears
  // 3 times, etc.
  let newVals = [];
  for (let i = 0; i < typeLists.length; i++) {
    let tl = typeLists[i];
    for (let token in tl) {
      let multip = "";
      if (tl[token] > 1) {
        multip = "(x" + tl[token] + ")";
      }
      newVals.push([token, multip, i])
    }
  }
  return newVals;
}

@Pipe({name: 'organizeOccurrenceColumns'})
export class OrganizeOccurrenceColumnsPipe implements PipeTransform {
  transform(values: string[], args: number[]): string[][] {
    let columnIndex: number = args[0];
    let organizeType: number = args[1];
    let combinedVals = combineSameValues(values, organizeType);
    // organize by location
    if (organizeType == 0) {
      if (combinedVals.length < 10) {
        if (columnIndex == 0) {
          return combinedVals.map((value) => {
            return [value[0], value[1]];
          });
        } else {
          return [];
        }
      }
      let columnSize = Math.ceil(combinedVals.length/4);
      return combinedVals.filter((value, index) => {
        return (index >= columnIndex*columnSize) && (index < (columnIndex+1)*columnSize);
      }).map((value) => {
        return [value[0], value[1]];
      });
    } else {
      return combinedVals.filter((value, index) => {
        return columnIndex == parseInt(value[2]);
      }).map((value) => {
        return [value[0], value[1]];
      });
    }
  }
}

// Extra pipe for organizing a small number of occurrences within a context
// Group displayed horizontally
@Pipe({name: 'organizeContextColumns'})
export class OrganizeContextColumnsPipe implements PipeTransform {
  transform(values: string[], args: number[]): string[][] {
    let columnIndex: number = args[0];
    let contextType: number = args[1];

    // Filter by context
    values = values.filter((value, index) => {
      return contextType == parseInt(value[1]);
    });

    // Combine values from the same section
    let combinedVals = combineSameValues(values, 0);

    // Organize into four columns
    let columnSize = Math.ceil(combinedVals.length/4);
    return combinedVals.filter((value, index) => {
      return (index >= columnIndex*columnSize) && (index < (columnIndex+1)*columnSize);
    }).map((value) => {
      return [value[0], value[1]];
    });
  }
}
