module.exports = [
  {
    ignores: [
      "vendor/**/**",
      "ui/js/vendor/**/*"
    ],
    languageOptions: {
      ecmaVersion: 2016,
      sourceType: "module",
      globals: {
        browser: "readonly",
        jQuery: "readonly",
        uPlot: "readonly",
        Choices: "readonly",
        parse: "readonly",
        "$": "readonly",
        document: "readonly",
        window: "readonly"
      },
    }
  },
  {
    files: [
      "ui/js/parts/*.js",
      "ui/js/parts/*/*.js",
      "ui/js/pages/*.js",
      "ui/js/endpoints/*.js"
    ],
    rules: {
      indent: ["error", 4, { SwitchCase: 1 }],
      "linebreak-style": ["error", "unix"],
      quotes: ["error", "single"],
      semi: ["error", "always"],
      "no-trailing-spaces": "error",
      "space-before-blocks": ["error", "always"],
      "space-in-parens": ["error", "never"],
      "brace-style": ["error", "1tbs", { allowSingleLine: true }],
      "keyword-spacing": ["error", { before: true, after: true }],
      "no-unused-vars": "off",
      "no-var": "error",
      "space-before-function-paren": ["error", {
        anonymous: "never",
        named: "never",
        asyncArrow: "always"
      }],
      "no-unused-expressions": ["error", {
        allowShortCircuit: true,
        allowTernary: true
      }],
      "no-undef": "off",
      "no-shadow": "error",
      "no-mixed-spaces-and-tabs": "error",
      "no-multi-spaces": "off",
      "no-useless-escape": "error",
      "no-return-await": "error",
      "prefer-arrow-callback": "warn",
      "object-curly-spacing": ["error", "never"],
      "array-bracket-spacing": ["error", "never"],
      "max-len": ["error", { code: 120, ignoreComments: true, ignoreUrls: true }],
      "comma-dangle": "off",
      "security/detect-object-injection": "off"
    }
  }
];
