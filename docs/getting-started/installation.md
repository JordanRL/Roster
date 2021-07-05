# Installing Roster

Roster is available through composer. To install, you can use the following command from the command-line:

`composer require-dev "samsara/roster:^0.1"`

Or you can add the following entry to your project's composer.json file:

```json
{
  "require-dev": {
    "samsara/roster": "^0.1"
  }
}
```

## Dependencies

This program requires the [samsara/mason](https://packagist.org/packages/samsara/mason) and [symfony/console](https://packagist.org/packages/symfony/console) packages. These are automatically resolved through composer.

Additionally, **PHP 8 is required to use this tool**. I know that a lot of people haven't migrated yet, but it offers some great features, and to use this program you only need to install it on your dev box, not your application server.