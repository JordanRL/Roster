# Roster | Docs From The Source

Roster is a PHP command line program that allows you to build documentation from your code. It inspects the files in your codebase and looks at the PHPDoc comments associated with them, and then builds documentation in Markdown.

But it doesn't have to.

The nice thing about Roster is that you can export the documentation however you please. It comes pre-configured with templates for Markdown, but it has a templating engine built in, so if you want to export the docs in a different format it's pretty simple to do (even if it would probably take some time).

## Built-In Integration With MkDocs

The python program [MkDocs](https://www.mkdocs.org/) is a great way to write documentation in Markdown and then export it to Github pages. The workflow is extremely simple, and if you're unfamiliar I highly encourage you to take a look.

The good news is that Roster includes a set of templates that will build out this exact documentation formatting you see here. Simply call Roster with the `--mkdocs` flag, and it will not only build the Markdown files, but also build your mkdocs.yml and requirements.txt files, as well as exporting a copy of the CSS used in this documentation.

!!! see-also "See Also"
    For more information on how to use the command, see the [Using the Command](getting-started/using-the-command.md) page.

## PHP 8 Ready

Or rather, PHP 8 *only*. There are lots of programs that can't move to PHP 8 quite yet, but your documentation can benefit from PHP 8 before your codebase does. Simply call the roster command using a PHP 8 binary from your development box, no need to upgrade your servers.

!!! see-also "See Also"
    For more information on installation and usage requirements, see the [Installation](getting-started/installation.md) page.

## Bleeding Edge

!!! warning "This program is in very early release"
    Roster has only been in development for a few weeks, so there are bound to be edge cases, quirks, and bugs that haven't been worked out. If you spot any, please make a [bug report](https://github.com/JordanRL/Roster/issues).

    The nice thing is, even if there are bugs, they won't affect your program. We're only building the documentation files after all!