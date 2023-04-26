<?php

namespace Lammensj\PropertyAccess\Services\PropertyAccessor;

use Drupal\Core\Utility\Token;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface as CorePropertyAccessorInterface;

class PropertyAccessor implements PropertyAccessorInterface {

  /**
   * Constructs a PropertyAccessor-instance.
   *
   * @param \Drupal\Core\Utility\Token $token
   *   The token utility.
   * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface|null $accessor
   *   The property accessor.
   * @param \Symfony\Component\ExpressionLanguage\ExpressionLanguage $language
   *   The expression language.
   * @param array $tokenData
   *   The token data.
   */
  public function __construct(
    protected Token $token,
    protected ?CorePropertyAccessorInterface $accessor = NULL,
    protected ExpressionLanguage $language = new ExpressionLanguage(),
    protected array $tokenData = []
  ) {
    $this->accessor = PropertyAccess::createPropertyAccessor();
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(mixed $target, int|array|string|null $key, $default = NULL): mixed {
    if (empty($key)) {
      return $target;
    }

    $key = is_array($key) ? $key : preg_split('/(?<![[)])\./', $key, -1, PREG_SPLIT_NO_EMPTY);
    while ($segment = array_shift($key)) {
      if ($segment === '*') {
        return $this->dataGetCollection($target, $key, $default);
      }

      // Replace Drupal-tokens before evaluating the expression.
      $segment = strval($this->token->replace($segment, $this->tokenData));

      // Determine whether the segment contains a language expression.
      $matches = [];
      $isExpression = (bool) preg_match('/(?<outer>\[(?<exp>(?>[^\[\]]+)|(?R))*\])/i', $segment, $matches);

      if ($isExpression) {
        $data = $this->dataGetFromExpression($target, $matches['exp'], $default);

        return $this->dataGetCollection($data, $key, $default);
      }

      $target = $this->dataGetFromAccessor($target, $segment, $default);
    }

    return $target;
  }

  /**
   * {@inheritdoc}
   */
  public function setTokenData(string $key, mixed $data): void {
    $this->tokenData[$key] = $data;
  }

  /**
   * Fetch a collection from the target.
   *
   * @param mixed $target
   *   The target.
   * @param int|array|string|null $key
   *   The key.
   * @param $default
   *   The default value.
   *
   * @return array
   *   Returns a collection.
   */
  protected function dataGetCollection(mixed $target, int|array|string|null $key, $default = NULL): array {
    if (!is_iterable($target)) {
      return value($default);
    }

    $result = array_reduce($target, function (array $carry, $item) use ($key, $default) {
      $carry[] = $this->getValue($item, $key, $default);

      return $carry;
    }, []);

    $shouldCollapse = (bool) preg_grep('/(\[((?>[^\[\]]+)|(?R))*\])/i', $key) || in_array('*', $key);

    return $shouldCollapse ? self::collapse($result) : $result;
  }

  /**
   * Fetch data via an expression.
   *
   * @param mixed $target
   *   The target.
   * @param string $expression
   *   The expression.
   * @param $default
   *   The default value.
   *
   * @return array
   *   Returns a (filtered) collection.
   */
  protected function dataGetFromExpression(mixed $target, string $expression, $default = NULL): array {
    // We can only execute a language expression on an iterable.
    if (!is_iterable($target)) {
      return value($default);
    }

    return array_filter($target, function ($item) use ($expression) {
      try {
        return !empty($this->language->evaluate(sprintf('object.%s', $expression), ['object' => (object) $item]));
      }
      catch (\Exception $e) {
        return FALSE;
      }
    });
  }

  /**
   * Fetch data via the property accessor.
   *
   * @param mixed $target
   *   The target.
   * @param int|array|string|null $segment
   *   The segment.
   * @param $default
   *   The default value.
   *
   * @return mixed
   *   Returns the value inside the target.
   */
  protected function dataGetFromAccessor(mixed $target, int|array|string|null $segment, $default = NULL): mixed {
    if (!is_object($target) && !is_array($target)) {
      return value($default);
    }

    try {
      return $this->accessor->getValue($target, $segment);
    }
    catch (NoSuchPropertyException $e) {
      try {
        return $this->accessor->getValue($target, sprintf('[%s]', $segment));
      }
      catch (\Exception $e) {
        return value($default);
      }
    }
    catch (\Exception $e) {
      return value($default);
    }
  }

  /**
   * Collapse an iterable of items into a single array.
   *
   * @param iterable $array
   *   The iterable.
   *
   * @return array
   *   The collapsed array.
   */
  protected static function collapse(iterable $array): array {
    $arrayIterator = new \RecursiveArrayIterator($array, \RecursiveArrayIterator::CHILD_ARRAYS_ONLY);
    $iterator = new \RecursiveIteratorIterator($arrayIterator);

    return iterator_to_array($iterator, FALSE);
  }

}