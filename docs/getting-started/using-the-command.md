# Using the Command

The most basic usage of the command from the CLI would be:

`php vendor/bin/roster src`

Assuming that 'src' is your root sources directory.

## Options

The basic format with options looks like this:

`php vendor/bin/roster [SOURCE_DIR] [OPTIONS]`

There are several options currently available on the command.

!!! signature constant "--templates|-t"
    default
    :   'doc-templates/roster-templates'

    This option allows you to specify a path to alternative templates if you've created your own.

    This option is ignored if you use the `--mkdocs` option.

    **NOTE:** You must have created your own version of every template file in order to use a different template directory. I'd suggest starting by copying the 'vendor/samsara/roster/docs/doc-templates/roster-templates' folder and making changes. The templating tokens are currently undocumented.

!!! signature constant "--visibility"
    default
    :   'all'

    values
    :   'all', 'protected', 'public'

    This option controls which visibility levels get included in the documentation. The setting tells the program what the *highest* level of visibility is that will be included.

    So using this option with 'public' will mean that *only* public methods and parameters are included in the documentation.

!!! signature constant "--prefer-source"
    default
    :   false

    This option controls whether or not Roster trusts the source files or the PHPDoc comments when the two have conflicting information. As the PHPDoc comments have access to a greater variety of data, including things like generic types that are not available in PHP currently, by default the comments are preferred.

!!! signature constant "--with-version"
    default
    :   null

    This option lets you specify a version number. The documentation will be exported into a folder with that name, making it easy to switch tags and then rebuild your docs for multiple versions.

    If this option is omitted, Roster will look in your composer.json file for a "version" value. If it can't find one there, the docs will be exported into the folder 'docs/roster/latest'

!!! signature constant "--with-debug"
    default
    :   false

    This option currently does nothing, but as Roster continue to improve more debugging information will become available during execution.

!!! signature constant "--mkdocs"
    default
    :   false

    This option ignores your templates if you've provided any and builds the documentation so that it's ready for you to immediately run the `mkdocs gh-deploy` command on it.

    It uses the doc templates stored in the vendor folder under 'doc-templates/roster-templates-mkdocs'.

    **NOTE:** MkDocs requires python, and the templates that are built additionally require the 'pymdown-extensions' module.