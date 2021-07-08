# Samsara\Roster > TemplateFactory

*No description available*


## Methods


### Static Methods

!!! signature "public TemplateFactory::pushTemplate(string $filePath, string $extension)"
    **$filePath**

    type
    :   string

    description
    :   *No description available*

    **$extension**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

---

!!! signature "public TemplateFactory::getTemplate(string $name)"
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   Samsara\Roster\Processors\TemplateProcessor|false

    description
    :   *No description available*

---

!!! signature "public TemplateFactory::queueCompile(string $path, Samsara\Roster\Processors\TemplateProcessor|Samsara\Roster\Processors\Base\BaseCodeProcessor $template, string $extension)"
    **$path**

    type
    :   string

    description
    :   *No description available*

    **$template**

    type
    :   Samsara\Roster\Processors\TemplateProcessor|Samsara\Roster\Processors\Base\BaseCodeProcessor

    description
    :   *No description available*

    **$extension**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

---

!!! signature "public TemplateFactory::hasTemplate(string $name)"
    **$name**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   bool

    description
    :   *No description available*

---

!!! signature "public TemplateFactory::compileAll(Symfony\Component\Console\Style\SymfonyStyle $io)"
    **$io**

    type
    :   Symfony\Component\Console\Style\SymfonyStyle

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

---

!!! signature "public TemplateFactory::getWrittenFiles()"
    **return**

    type
    :   array

    description
    :   *No description available*

---

!!! signature "public TemplateFactory::writeToDocs(string $writePath, Symfony\Component\Console\Style\SymfonyStyle $io)"
    **$writePath**

    type
    :   string

    description
    :   *No description available*

    **$io**

    type
    :   Symfony\Component\Console\Style\SymfonyStyle

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

---




---
!!! footer-link "This documentation was generated with [Roster](https://jordanrl.github.io/Roster/)."