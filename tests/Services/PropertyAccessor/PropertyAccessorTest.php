<?php

namespace Lammensj\PropertyAccess\Tests\Services\PropertyAccessor;

use Lammensj\PropertyAccess\Services\PropertyAccessor\PropertyAccessor;
use Lammensj\PropertyAccess\Services\PropertyAccessor\PropertyAccessorInterface;
use Lammensj\PropertyAccess\Tests\Fixtures\BaseModel;
use PHPUnit\Framework\TestCase;

/**
 * @group property_access
 */
class PropertyAccessorTest extends TestCase {

  /**
   * The property accessor.
   *
   * @var \Lammensj\PropertyAccess\Services\PropertyAccessor\PropertyAccessorInterface|NULL
   */
  protected ?PropertyAccessorInterface $accessor;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->accessor = new PropertyAccessor();
  }

  /**
   * @dataProvider getValidPropertyPaths
   */
  public function testGetValue($target, $path, $value) {
    $this->assertSame($value, $this->accessor->getValue($target, $path));
  }

  public function getValidPropertyPaths(): \Generator {
    $names = $this->generateNames();

    yield [
      new BaseModel([new BaseModel([new BaseModel($names[0])])]),
      sprintf('value.*.value.[getValue()=="%s"].value', $names[0]),
      [$names[0]],
    ];

    $model = new BaseModel($names[0]);
    yield [
      new BaseModel([new BaseModel([$model])]),
      sprintf('value.*.value.[getValue()=="%s"]', $names[0]),
      [$model],
    ];

    yield [
      new BaseModel([new BaseModel($names[0]), new BaseModel($names[1]), new BaseModel($names[2])]),
      sprintf('value.[getValue()=="%s"].value', $names[1]),
      [$names[1]],
    ];

    yield [
      new BaseModel([new BaseModel(new BaseModel($names[0])), new BaseModel($names[1]), new BaseModel($names[2])]),
      'value.*.value.value',
      [$names[0], NULL, NULL],
    ];

    yield [
      new BaseModel([new BaseModel($names[0]), new BaseModel($names[1]), new BaseModel($names[2])]),
      'value.*.value',
      [$names[0], $names[1], $names[2]],
    ];

    yield [
      new BaseModel(['person' => new BaseModel($names[0])]),
      'value.person.value',
      $names[0],
    ];

    yield [
      new BaseModel(new BaseModel($names[0])),
      'value.value',
      $names[0],
    ];

    yield [
      new BaseModel((object) ['person' => (object) ['name' => $names[0]]]),
      'value.person.name',
      $names[0],
    ];

    yield [
      new BaseModel(['person' => ['name' => $names[0]]]),
      'value.person.name',
      $names[0],
    ];

    yield [
      new BaseModel(['person' => ['name' => $names[0]]]),
      'value.person.name',
      $names[0],
    ];

    yield [
      new BaseModel($names[0]),
      'value',
      $names[0],
    ];

    yield [
      (object) ['person' => (object) ['name' => $names[0]]],
      'person.name',
      $names[0],
    ];

    yield [
      ['person' => (object) ['name' => $names[0]]],
      'person.name',
      $names[0],
    ];

    yield [
      ['person' => ['name' => $names[0]]],
      'person.name',
      $names[0],
    ];
  }

  /**
   * Generate a collection of names.
   *
   * @param int $count
   *   The amount of names to generate.
   *
   * @return string[]
   *   Returns a collection of names.
   */
  protected function generateNames(int $count = 5): array {
    $names = array_fill(0, $count, NULL);

    return array_map(fn () => $this->getRandomGenerator()->name(), $names);
  }

}
