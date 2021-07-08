# Samsara\Roster\Processors > ClassProcessor

This class takes in a class reflector and builds out the entire doc for that class, including all subdocs.


## Inheritance


### Extends

- Samsara\Roster\Processors\Base\BaseCodeProcessor


## Variables & Data


### Inherited Properties

!!! signature property "protected BaseCodeProcessor->declaringClass"
    ##### declaringClass
    type
    :   string

    value
    :   ''

!!! signature property "protected BaseCodeProcessor->docBlock"
    ##### docBlock
    type
    :   Samsara\Mason\DocBlockProcessor

    value
    :   *uninitialized*

!!! signature property "protected BaseCodeProcessor->templateProcessor"
    ##### templateProcessor
    type
    :   Samsara\Roster\Processors\TemplateProcessor

    value
    :   *uninitialized*



## Methods


### Constructor

!!! signature "public ClassProcessor->__construct(ReflectionClass $class, string $templateName)"
    ##### __construct
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

    ###### __construct() Description:

    ClassProcessor constructor
    
---



### Instanced Methods

!!! signature "public ClassProcessor->compile()"
    ##### compile
    **return**

    type
    :   string

    description
    :   *No description available*
    
---

!!! signature "protected ClassProcessor->buildClassInfo()"
    ##### buildClassInfo
    **return**

    type
    :   *mixed* (assumed)

    description
    :   *No description available*
    
---



### Inherited Methods

!!! signature "public BaseCodeProcessor->getDeclaringClass()"
    ##### getDeclaringClass
    **return**

    type
    :   string

    description
    :   *No description available*
    
---

!!! signature "protected BaseCodeProcessor->fixDefaultValue($defaultValue)"
    ##### fixDefaultValue
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
    ##### fixOutput
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
    ##### templateLoader
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