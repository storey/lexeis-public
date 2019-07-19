// Adapted from https://scotch.io/tutorials/responsive-equal-height-with-angular-directive
import {
    Directive, ElementRef, AfterViewInit,Input, HostListener
} from '@angular/core';

@Directive({
    selector: '[appMatchHeight]'
})

export class MatchHeightDirective implements AfterViewInit {
  // class name to match height
  @Input()
  appMatchHeight: string;

  constructor(
    private el: ElementRef
  ) {}

  // Update heights after creation
  ngAfterViewInit() {
    this.matchHeight(this.el.nativeElement, this.appMatchHeight);
  }

  // Update heights on resize
  @HostListener('window:resize')
  onResize() {
    // call our matchHeight function here
    this.matchHeight(this.el.nativeElement, this.appMatchHeight);
  }

  // Match the heights of all the children of parent with class className
  matchHeight(parent: HTMLElement, className: string) {
    const BASE_CLASS_NAME = "baseColumn";
    const MIN_HEIGHT = 400;

    if (!parent) return;

    // step 1a: find all the child elements with the selected class name
    const children = parent.getElementsByClassName(className);

    if (!children) return;

    // step 1b: reset all children height
      Array.from(children).forEach((x: HTMLElement) => {
          x.style.height = 'auto';
      });

    // step 2: find out height of the column we are matching
    let heightToMatch = parent.getElementsByClassName(BASE_CLASS_NAME)[0].getBoundingClientRect().height;

    // Make sure
    if (heightToMatch < MIN_HEIGHT) {
      heightToMatch = MIN_HEIGHT;
    }

    //console.log("Height: " + heightToMatch);

    // step 3: update all the child elements to the correct height
    Array.from(children).forEach((x: HTMLElement) => {
      if (x.getBoundingClientRect().height > heightToMatch) {
        x.style.height = `${heightToMatch}px`;
      }
    });
  }
}
