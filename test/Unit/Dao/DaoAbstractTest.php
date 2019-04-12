<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Dao;

use Mockery;
use Mockery\MockInterface;
use YapepBase\Database\ConnectionHandler;
use YapepBase\Test\Unit\TestAbstract;

class DaoAbstractTest extends TestAbstract
{
    /** @var MockInterface */
    protected $connection;
    /** @var DaoStub */
    protected $dao;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = Mockery::mock(ConnectionHandler::class);

        $this->dao = new DaoStub();
        $this->dao->setConnection($this->connection);
    }

    public function emptyExpectationProvider(): array
    {
        return [
            'empty string considered empty' => ['', false],
            'null considered empty'         => [null, true],
        ];
    }

    /**
     * @dataProvider emptyExpectationProvider
     */
    public function testSetEqualityConditionWhenNoExpectationGiven_shouldSetNothing($expectation, bool $onlyNullConsideredEmpty)
    {
        $conditions = $queryParams = [];
        $this->dao->setEqualityCondition($conditions, $queryParams, 'field', $expectation, '', $onlyNullConsideredEmpty);

        $this->assertEmpty($conditions);
        $this->assertEmpty($queryParams);
    }

    public function testWhenBoolExpectationGiven_shouldTranslateToInt()
    {
        $conditions = $queryParams = [];

        $this->expectGetParamPrefix('_');
        $this->dao->setEqualityCondition($conditions, $queryParams, 'field', true);

        $expectedConditions  = ['field = :_field'];
        $expectedQueryParams = ['field' => 1];

        $this->assertSame($expectedConditions, $conditions);
        $this->assertSame($expectedQueryParams, $queryParams);
    }

    public function testWhenAliasProvided_shouldIncludeAliasInCondition()
    {
        $conditions = $queryParams = [];

        $this->expectGetParamPrefix('_');
        $this->dao->setEqualityCondition($conditions, $queryParams, 'field', 1, 'A');

        $expectedConditions  = ['A.field = :_A_field'];
        $expectedQueryParams = ['A_field' => 1];

        $this->assertSame($expectedConditions, $conditions);
        $this->assertSame($expectedQueryParams, $queryParams);
    }

    public function testWhenEmptyExpectationGivenButOnlyNullConsideredEmpty_shouldSetExpectation()
    {
        $conditions = $queryParams = [];

        $this->expectGetParamPrefix('_');
        $this->dao->setEqualityCondition($conditions, $queryParams, 'field', 0, '', true);

        $expectedConditions  = ['field = :_field'];
        $expectedQueryParams = ['field' => 0];

        $this->assertSame($expectedConditions, $conditions);
        $this->assertSame($expectedQueryParams, $queryParams);
    }

    public function testSetInListConditionWhenEmptyValuesGiven_shouldDoNothing()
    {
        $conditions = $queryParams = [];
        $this->dao->setInListCondition($conditions, $queryParams, 'field', []);

        $this->assertEmpty($conditions);
        $this->assertEmpty($queryParams);
    }

    public function testSetInListConditionWhenCalled_shouldSetInListCondition()
    {
        $conditions = $queryParams = [];
        $this->expectGetParamPrefix('');
        $this->dao->setInListCondition($conditions, $queryParams, 'field', [1, 2]);

        $expectedConditions  = ['field IN (:field_0, :field_1)'];
        $expectedQueryParams = ['field_0' => 1, 'field_1' => 2];

        $this->assertSame($expectedConditions, $conditions);
        $this->assertSame($expectedQueryParams, $queryParams);
    }

    public function testSetInListConditionWhenAliasGiven_shouldIncludeAliasInCondition()
    {
        $conditions = $queryParams = [];
        $this->expectGetParamPrefix('');
        $this->dao->setInListCondition($conditions, $queryParams, 'field', [1, 2], 'A');

        $expectedConditions  = ['A.field IN (:A_field_0, :A_field_1)'];
        $expectedQueryParams = ['A_field_0' => 1, 'A_field_1' => 2];

        $this->assertSame($expectedConditions, $conditions);
        $this->assertSame($expectedQueryParams, $queryParams);
    }

    public function testSetInListConditionWhenNegated_shouldSetNotInCondition()
    {
        $conditions = $queryParams = [];
        $this->expectGetParamPrefix('');
        $this->dao->setInListCondition($conditions, $queryParams, 'field', [1, 2], '', true);

        $expectedConditions  = ['field NOT IN (:field_0, :field_1)'];
        $expectedQueryParams = ['field_0' => 1, 'field_1' => 2];

        $this->assertSame($expectedConditions, $conditions);
        $this->assertSame($expectedQueryParams, $queryParams);
    }

    public function prefixProvider(): array
    {
        $fieldName = 'field';

        return [
            'empty alias'     => ['', $fieldName, $fieldName],
            'not empty alias' => ['A', $fieldName, 'A.field'],
        ];
    }

    /**
     * @dataProvider prefixProvider
     */
    public function testGetPrefixedField_shouldAddTableAliasWhenNotEmpty(string $tableAlias, string $fieldName, string $expectedResult)
    {
        $result = $this->dao->getPrefixedField($tableAlias, $fieldName);

        $this->assertSame($expectedResult, $result);
    }

    public function paramNameProvider(): array
    {
        $fieldName = 'field';

        return [
            'just field given'  => [$fieldName, '',  null, $fieldName],
            'table alias given' => [$fieldName, 'A', null, 'A_field'],
            'index given'       => [$fieldName, '',  1,    'field_1'],
            'all given'         => [$fieldName, 'A', 1,    'A_field_1'],
        ];
    }

    /**
     * @dataProvider paramNameProvider
     */
    public function testGetParamName_shouldGenerateUniqueParamName(string $fieldName, string $tableAlias, ?int $index, string $expectedResult)
    {
        $result = $this->dao->getParamName($fieldName, $tableAlias, $index);

        $this->assertSame($expectedResult, $result);
    }

    protected function expectGetParamPrefix(string $expectedResult)
    {
        $this->connection
            ->shouldReceive('getParamPrefix')
            ->once()
            ->andReturn($expectedResult);
    }
}
