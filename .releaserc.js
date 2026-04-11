module.exports = {
  plugins: [
    "@semantic-release/commit-analyzer",
    "@semantic-release/release-notes-generator",
    [
      "@google/semantic-release-replace-plugin",
      {
        "replacements": [
          {
            "files": ["getblitz-payment-gateway.php"],
            "from": /Version: \d+\.\d+\.\d+/g,
            "to": "Version: ${nextRelease.version}",
            "results": [
              {
                "file": "getblitz-payment-gateway.php",
                "hasChanged": true,
                "numMatches": 1,
                "numReplacements": 1
              }
            ],
            "countMatches": true
          },
          {
            "files": ["getblitz-payment-gateway.php"],
            "from": /define\('GETBLITZ_VERSION', '\d+\.\d+\.\d+'\);/g,
            "to": "define('GETBLITZ_VERSION', '${nextRelease.version}');",
            "results": [
              {
                "file": "getblitz-payment-gateway.php",
                "hasChanged": true,
                "numMatches": 1,
                "numReplacements": 1
              }
            ],
            "countMatches": true
          },
          {
            "files": ["readme.txt"],
            "from": /Stable tag: \d+\.\d+\.\d+/g,
            "to": "Stable tag: ${nextRelease.version}",
            "results": [
              {
                "file": "readme.txt",
                "hasChanged": true,
                "numMatches": 1,
                "numReplacements": 1
              }
            ],
            "countMatches": true
          }
        ]
      }
    ],
    [
      "@semantic-release/exec",
      {
        "prepareCmd": "sh bin/build-zip.sh"
      }
    ],
    [
      "@semantic-release/changelog",
      {
        "changelogFile": "CHANGELOG.md"
      }
    ],
    [
      "@semantic-release/git",
      {
        "assets": [
          "CHANGELOG.md",
          "readme.txt",
          "getblitz-payment-gateway.php"
        ],
        "message": "chore(release): ${nextRelease.version} [skip ci]\n\n${nextRelease.notes}"
      }
    ],
    [
      "@semantic-release/github",
      {
        "assets": [
          {
            "path": "getblitz-payment-gateway.zip",
            "name": "getblitz-payment-gateway-${nextRelease.version}.zip",
            "label": "GetBlitz Payment Gateway plugin"
          }
        ]
      }
    ]
  ]
};
