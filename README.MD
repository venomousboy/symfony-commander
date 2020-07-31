# Venomousboy/Commander

Fill DTO from symfony request

### Description on property
How annotation reading works and how to pass data types?

Use these annotations
string types:

`````@Property(type="?string")`````

if you are using an array, specify:

`````@Property(type="string[]")`````

Integer type in property 

`````@Property(type="int")`````

Or you are using int array:

`````@Property(type="int[]") `````

Float type using:

`````@Property(type="float") `````

Object type using:

`````@Property(type="?\App\Object", isStructure=true) `````

### What is ```"isStructure=true"```
This type specifies that the property has an object that also has type property annotations ``@Property()``
### Installation
```bash
composer require venomousboy/symfony-commander
```

### In Symfony 
*config/service.yaml*
```yaml
    Venomousboy\Commander\Commander:
        public: true
        class: Venomousboy\Commander\Commander
        arguments:
            - '@request_stack'
```

### Usage
```php
<?php

namespace App\DTO\CreateSomeCommand;

final class CreateSomeCommand
{
    /**
     * @Property(type="int")
     */
    private int $prop1;
    
    /**
     * @Property(type="bool")
     */
    private bool $prop2;
    
    /**
     * @Property(type="?string")
     */
    private ?string $prop3;
    
    
    /**
     * @Property(type="?float")
     */
    private ?float $prop4;    
    
    /**
     * @var int[]
     *
     * @Property(type="int[]")
     */
    private array $prop5;
    
    /**
     * @Property(type="?\App\Frontend\Address", isStructure=true)
     */
    private ?Address $address;

    ... your getter
}
````
````php
namespace App\DTO\Address;

final class Address
{
    /**
    * @Property(type="string")
    */
    private string $city;
    
    /**
    * @Property(type="int")
    */
    private int $home;

    ... your getter
}

````
### You controller
```php

namespace App\Controller\SomeController;

Venomousboy\Commander\Commander;

final class SomeController
{
    private Commander $commander;
    
    public function __construct(Commander $commander)
    {
	$this->commander = $commander;
    }
    
    public function create(Request $request)
    {
        $command = new CreateSomeCommand();
        $this->commander->fill($command);
        $this->service->createExample($command);
    }
    
   
}
```
### You service

```php

namespace App\Service;

use App\Entity\SomeEntity;

final class SomeService
{
    
    /**
    * This create example method your service
    * @param $command
    * @return int
    */
    public function createExample($command): int
    {
        $entity = new SomeEntity(
            $command->getName(),
            $command->getDescription(),
            $command->getPrice()
        );
    }

}
```
