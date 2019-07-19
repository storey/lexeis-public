import { Component } from '@angular/core';

@Component({
  selector: 'betacode',
  templateUrl: './betacode.component.html',
  styleUrls: [ './betacode.component.css' ]
})

export class BetacodeComponent {
  public pairs_beta: any = [
    { "greek": "α", "beta": "a" },
    { "greek": "β", "beta": "b" },
    { "greek": "γ", "beta": "g" },
    { "greek": "δ", "beta": "d" },
    { "greek": "ε", "beta": "e" },
    { "greek": "ζ", "beta": "z" },
    { "greek": "η", "beta": "h" },
    { "greek": "θ", "beta": "q" },
    { "greek": "ι", "beta": "i" },
    { "greek": "κ", "beta": "k" },
    { "greek": "λ", "beta": "l" },
    { "greek": "μ", "beta": "m" },
    { "greek": "ν", "beta": "n" },
    { "greek": "ξ", "beta": "c" },
    { "greek": "ο", "beta": "o" },
    { "greek": "π", "beta": "p" },
    { "greek": "ρ", "beta": "r" },
    { "greek": "σ", "beta": "s" },
    { "greek": "ς", "beta": "s" },
    { "greek": "τ", "beta": "t" },
    { "greek": "υ", "beta": "u" },
    { "greek": "φ", "beta": "f" },
    { "greek": "χ", "beta": "x" },
    { "greek": "ψ", "beta": "y" },
    { "greek": "ω", "beta": "w" },
    { "greek": "ἀ", "beta": "a)" },
    { "greek": "ἁ", "beta": "a(" },
    { "greek": "ά", "beta": "a/" },
    { "greek": "ὰ", "beta": "a\\" },
    { "greek": "ᾶ", "beta": "a=" },
    { "greek": "ᾳ", "beta": "a|" },
    { "greek": "ἄ", "beta": "a)/" },
    { "greek": "ἅ", "beta": "a(/" },
    { "greek": "ἂ", "beta": "a)\\" },
    { "greek": "ἃ", "beta": "a(\\" },
    { "greek": "ἆ", "beta": "a)=" },
    { "greek": "ἇ", "beta": "a(=" },
    { "greek": "ᾆ", "beta": "a)=|" },
    { "greek": "Α", "beta": "*a" },
    { "greek": "Β", "beta": "*b" },
    { "greek": "[capital letter]", "beta": "*[lowecase betacode]" },
  ];
  public pairs_latin: any = [
    { "greek": "α", "latin": "a" },
    { "greek": "β", "latin": "b" },
    { "greek": "γ", "latin": "g" },
    { "greek": "δ", "latin": "d" },
    { "greek": "ε", "latin": "e" },
    { "greek": "ζ", "latin": "z" },
    { "greek": "η", "latin": "e" },
    { "greek": "θ", "latin": "th" },
    { "greek": "ι", "latin": "i" },
    { "greek": "κ", "latin": "k" },
    { "greek": "λ", "latin": "l" },
    { "greek": "μ", "latin": "m" },
    { "greek": "ν", "latin": "n" },
    { "greek": "ξ", "latin": "x" },
    { "greek": "ο", "latin": "o" },
    { "greek": "π", "latin": "p" },
    { "greek": "ρ", "latin": "r" },
    { "greek": "σ", "latin": "s" },
    { "greek": "ς", "latin": "s" },
    { "greek": "τ", "latin": "t" },
    { "greek": "υ", "latin": "u" },
    { "greek": "φ", "latin": "ph" },
    { "greek": "χ", "latin": "kh" },
    { "greek": "ψ", "latin": "ps" },
    { "greek": "ω", "latin": "o" },
    { "greek": "ἀ", "latin": "a" },
    { "greek": "ἁ", "latin": "ha" },
    { "greek": "ά, ὰ, ᾶ", "latin": "a" },
    { "greek": "ᾳ", "latin": "ai" },
  ];
}