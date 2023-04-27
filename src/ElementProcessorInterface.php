<?php

namespace Lammensj\PropertyAccess;

interface ElementProcessorInterface {

  /**
   * Processes the element.
   *
   * @param string $element
   *   The element.
   *
   * @return string
   *   Returns the altered element.
   */
  public function process(string $element): string;

}
