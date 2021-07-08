# Samsara\Roster\Processors > ClassProcessor

This class takes in a class reflector and builds out the entire doc for that class, including all subdocs.


## Inheritance


### Extends

- Samsara\Roster\Processors\Base\BaseCodeProcessor


## Variables & Data


### Inherited Properties

!!! signature property "protected BaseCodeProcessor->declaringClass"
    type
    :   string

    value
    :   ''

!!! signature property "protected BaseCodeProcessor->docBlock"
    type
    :   Samsara\Mason\DocBlockProcessor

    value
    :   *uninitialized*

!!! signature property "protected BaseCodeProcessor->templateProcessor"
    type
    :   Samsara\Roster\Processors\TemplateProcessor

    value
    :   *uninitialized*



## Methods


### Constructor

!!! signature "public ClassProcessor->__construct(ReflectionClass $class, string $templateName)"
    **$class**

    type
    :   ReflectionClass

    description
    :   This is the reflection class of the class that you want to build a doc from.

    **$templateName**

    type
    :   string

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

    **ClassProcessor->__construct Description**

    ClassProcessor constructor

---



### Instanced Methods

!!! signature "public ClassProcessor->compile()"
    **return**

    type
    :   string

    description
    :   *No description available*

---

!!! signature "protected ClassProcessor->buildClassInfo()"
    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

---



### Inherited Methods

!!! signature "public BaseCodeProcessor->getDeclaringClass()"
    **return**

    type
    :   string

    description
    :   *No description available*

---

!!! signature "protected BaseCodeProcessor->fixDefaultValue($defaultValue)"
    **$defaultValue**

    description
    :   *No description available*

    **return**

    type
    :   string

    description
    :   *No description available*

---

!!! signature "protected BaseCodeProcessor->fixOutput($option1, $option2, $option3)"
    **$option1**

    description
    :   *No description available*

    **$option2**

    description
    :   *No description available*

    **$option3**

    description
    :   *No description available*

    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*

---

!!! signature "protected BaseCodeProcessor->templateLoader(string $templateName)"
    **$templateName**

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




---
!!! footer-link "This documentation was generated with [Roster](https://jordanrl.github.io/Roster/)."