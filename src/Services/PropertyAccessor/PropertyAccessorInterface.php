<?php

namespace Lammensj\PropertyAccess\Services\PropertyAccessor;

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
   * Set token data.
   *
   * @param string $key
   *   The key.
   * @param mixed $data
   *   The data.
   */
  public function setTokenData(string $key, mixed $data): void;

}
