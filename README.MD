# Venomousboy/Commander

Fill DTO from symfony request

### Installation
```bash
composer require venomousboy/symfony-commander
```

in Symfony
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

namespace App\Frontend;

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

    ...
}

<?php

namespace App\Frontend;

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
    }
    
    ...
}
```
