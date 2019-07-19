export var LAYOUT = {
  "STANDARD": "STANDARD",
  "NARROW": "NARROW",
  "FULL": "FULL"
};

// Store classes for child divs of main container in each situation
export var MAIN_CONTAINER_1 = {
  "STANDARD": "container-fluid",
  "NARROW": "container-fluid",
  "FULL": ""
}

export var MAIN_CONTAINER_2 = {
  "STANDARD": "row justify-content-center",
  "NARROW": "row justify-content-center",
  "FULL": ""
}

export var MAIN_CONTAINER_3 = {
  "STANDARD": "col-12 col-sm-12 col-md-10 col-lg-8 col-xl-8",
  "NARROW": "col-12 col-sm-12 col-md-8 col-lg-6 col-xl-6",
  "FULL": ""
}

// alphabet
export var ALPHA_UPPER = ["Α", "Β", "Γ", "Δ", "Ε", "Ζ", "Η", "Θ", "Ι", "Κ", "Λ", "Μ", "Ν", "Ξ", "Ο", "Π", "Ρ", "Σ", "Τ", "Υ", "Φ", "Χ", "Ψ", "Ω"];
export var ALPHA_LOWER = ["_", "α", "β", "γ", "δ", "ε", "ζ", "η", "θ", "ι", "κ", "λ", "μ", "ν", "ξ", "ο", "π", "ρ", "σ", "τ", "υ", "φ", "χ", "ψ", "ω"];

// Semantic group color option indices
export var SEMANTIC_GROUP_COLOR_OPTIONS = [
  "0",  // White
  "1",  // Black
  "2",  // Yellow
  "3",  // Purple
  "4",  // Orange
  "5",  // Light Blue
  "6",  // Red
  "7",  // Buff
  "8",  // Gray
  "9",  // Green
  "10", // Purplish Pink
  "11", // Blue
  "12", // Yellowish Pink
  "13", // Violet
  "14", // Orange Yellow
  "15", // Purplish Red
  "16", // Greenish Yellow
  "17", // Reddish Brown
  "18", // Yellow Green
  "19", // Yellowish Brown
  "20", // Reddish Orange
  "21", // Olive Green
];

export var boolToEnglish = function(b: boolean): string {
  if (b) {
    return "Yes";
  } else {
    return "No";
  }
};
