<?php

namespace Lammensj\PropertyAccess\Services\PropertyAccessor;

use Lammensj\PropertyAccess\ElementProcessorType;
use Lammensj\PropertyAccess\ElementProcessorInterface;

interface PropertyAccessorInterface {

  /**
   * Get an item from an array or object using "dot" notation.
   *
   * @param mixed $target
   *   The data object from which to fetch values.
   * @param array|int|null|string $key
   *   The key pointing to the value.
   * @param $default
   *   An optional default value.
   *
   * @return mixed
   *   Returns the found value or the default if one is provided.
   *
   * @see data_get
   */
  public function getValue(mixed $target, array|int|null|string $key, $default = NULL): mixed;

  /**
   * Add a processor.
   *
   * @param \Lammensj\PropertyAccess\ElementProcessorInterface $processor
   *   The processor.
   * @param \Lammensj\PropertyAccess\ElementProcessorType $type
   *   The type.
   * @param int $priority
   *   The priority.
   *
   * @return \Lammensj\PropertyAccess\Services\PropertyAccessor\PropertyAccessorInterface
   *   Returns the called object.
   */
  public function addProcessor(ElementProcessorInterface $processor, ElementProcessorType $type, int $priority = 0): PropertyAccessorInterface;

}
