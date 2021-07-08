# Using the Config File

If there is a roster.json file in your project root, Roster will attempt to use that file to configure the documentation build. You can also specify a config file using the `--config-file` option.

## Configuration Schema

The repository contains a JSON Schema definition, in JSON Schema version 7.

This file can be downloaded from here:

[https://raw.githubusercontent.com/JordanRL/Roster/master/roster-config-schema.config.json](https://raw.githubusercontent.com/JordanRL/Roster/master/roster-config-schema.config.json)

This can be used to help your IDE validate your configuration file as you create it.

## Example Config File With All Options

Below is an example config file with all options given values to illustrate how the config file can be written.

```json
{
  "prefer-source": false,
  "with-version": "v0.2",
  "templates": "doc-templates/roster-templates-mkdocs",
  "mkdocs": {
    "site-name": "Roster - Docs From The Source",
    "site-url": "https://jordanrl.github.io/Roster/",
    "repo-url": "https://github.com/JordanRL/Roster/",
    "theme": "sphinx-rtd",
    "auto-deploy": true,
    "merge-nav": true,
    "merge-nav-mode": "replace-nav-key",
    "nav-key": "Source Reference"
  },
  "sources": [
    {
      "path": "./src",
      "visibility": "protected",
      "aliases": [
        {
          "namespace": "Samsara\\Roster\\",
          "alias": "Core\\"
        }
      ]
    },
    {
      "path": "../RosterModule/src",
      "visibility": "public",
      "aliases": [
        {
          "namespace": "Samsara\\Roster\\",
          "alias": "Module\\"
        }
      ]
    }
  ]
}
```