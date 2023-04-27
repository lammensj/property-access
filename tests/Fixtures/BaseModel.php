<?php

namespace Lammensj\PropertyAccess\Tests\Fixtures;

class BaseModel {

  /**
   * Constructs a BaseModel-instance.
   *
   * @param mixed $value
   *   The value.
   */
  public function __construct(
    protected mixed $value
  ) {
  }

  /**
   * Get the value.
   *
   * @return mixed
   *   Returns the value.
   */
  public function getValue(): mixed {
    return $this->value;
  }

}
