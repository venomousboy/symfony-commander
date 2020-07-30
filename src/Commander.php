<?php

declare(strict_types=1);

namespace Venomousboy\Commander;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class Commander
{
    private Request $request;

    /**
     * @throws \RuntimeException
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();

        if ($this->request === null) {
            throw new \RuntimeException('There is no current request.');
        }
    }

    /**
     * @param object $object
     * @throws \ReflectionException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function fill(object $object): void
    {
        $request = $this->getRequest()->request->all();

        if (empty($this->getRequest()->request->all())) {
            $request = json_decode($this->getRequest()->getContent(), true);
            if ($request === null) {
                throw new \RuntimeException('Request is empty');
            }
        }

        $this->fillObject($object, $request);
    }

    /**
     * @param object $object
     * @param mixed[] $params
     * @throws \ReflectionException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function fillObject(object $object, array $params): void
    {
        $reflection = ($object);
        $properties = $reflection->getProperties();

        $this->fillProperties($properties, $object, $params);

        while (($reflection = $reflection->getParentClass()) !== false) {
            $properties = $reflection->getProperties();
            $this->fillProperties($properties, $object, $params);
        }
    }

    /**
     * @param \ReflectionProperty[] $properties
     * @param mixed[] $params
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function fillProperties(array $properties, object $object, array $params): void
    {
        $annotationReader = new AnnotationReader();

        foreach ($properties as $property) {
            /** @var Property|null $annotation */
            $annotation = $annotationReader->getPropertyAnnotation($property, Property::class);

            if ($annotation === null) {
                continue;
            }

            if ($annotation->name === null) {
                $annotation->name = $property->getName();
            }

            if ($annotation->type === null) {
                $annotation->type = sprintf(
                    '%s%s',
                    $property->getType()->allowsNull() ? '?' : '',
                    $property->getType()->getName()
                );
            }

            if ($annotation->isList()) {
                $this->fillListProperty($property, $annotation, $object, $params);
            } else {
                $this->fillProperty($property, $annotation, $object, $params);
            }
        }
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function fillProperty(\ReflectionProperty $property, Property $annotation, object $object, array $params): void
    {
        $input = $params[$annotation->name] ?? null;

        if ($input === null && !$annotation->isNullable()) {
            throw new \RuntimeException(sprintf('Missing required parameter "%s"', $annotation->name));
        }

        $value = $this->getValue($annotation, $input);

        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function fillListProperty(\ReflectionProperty $property, Property $annotation, object $object, array $params): void
    {
        $input = $params[$annotation->name] ?? [];

        if (!is_array($input)) {
            throw new \RuntimeException(sprintf('Parameter "%s" is expected to be array', $annotation->name));
        }

        $list = [];

        foreach ($input as $item) {
            $list[] = $this->getValue($annotation, $item);
        }

        $property->setAccessible(true);
        $property->setValue($object, $list);
    }

    /**
     * @param mixed $input
     * @return mixed|null
     * @throws \ReflectionException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function getValue(Property $annotation, /* mixed */ $input)
    {
        if ($input === null) {
            return null;
        }

        $type = $annotation->getTypeName();

        switch ($type) {
            case 'string':
                if (!is_string($input)) {
                    throw new \RuntimeException(sprintf('Parameter "%s" is expected to be string', $annotation->name));
                }

                return $input;
            case 'int':

                return (int) $input;
            case 'float':
                if (!is_int($input) && !is_float($input)) {
                    throw new \RuntimeException(sprintf('Parameter "%s" is expected to be float', $annotation->name));
                }

                return (float) $input;
            case 'bool':

                return (bool) $input;
        }

        if (!class_exists($type)) {
            throw new \RuntimeException(sprintf('Class not found: "%s"', $type));
        }

        if ($annotation->isStructure) {
            $object = new $type();
            $this->fillObject($object, $input);

            return $object;
        }

        $constructor = $annotation->constructor;

        if ($constructor === null) {
            return new $type($input);
        }

        return $type::$constructor($input);
    }

    private function getRequest(): Request
    {
        return $this->request;
    }
}
