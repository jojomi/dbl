<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Tests\Query;

use Jojomi\Dbl\Statement\AndCondition;
use Jojomi\Dbl\Statement\Comparison;
use Jojomi\Dbl\Statement\ComparisonType;
use Jojomi\Dbl\Statement\Eq;
use Jojomi\Dbl\Statement\Field;
use Jojomi\Dbl\Statement\FieldComparisonParam;
use Jojomi\Dbl\Statement\In;
use Jojomi\Dbl\Statement\IsNotNull;
use Jojomi\Dbl\Statement\IsNull;
use Jojomi\Dbl\Statement\Join;
use Jojomi\Dbl\Statement\JoinType;
use Jojomi\Dbl\Statement\NamedParam;
use Jojomi\Dbl\Statement\NotIn;
use Jojomi\Dbl\Statement\OrCondition;
use Jojomi\Dbl\Statement\Order;
use Jojomi\Dbl\Statement\OrderType;
use Jojomi\Dbl\Statement\Statement;
use Jojomi\Dbl\Statement\StatementBuilder;
use Jojomi\Dbl\Statement\Table;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StatementBuilderTest extends TestCase
{

    #[DataProvider('provideRender')]
    public function testRender(Statement $statement, string $expected): void
    {
        self::assertEquals($expected, $statement->render());
    }

    /**
     * @return iterable<int, array{0: \Jojomi\Dbl\Statement\Statement, 1: string}>
     */
    public static function provideRender(): iterable
    {
        yield [
            StatementBuilder::select()
                ->from('articles')
                ->fields('id'),
            'SELECT `id` FROM `articles`;',
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked(Table::create('articles', 'a'))
                ->fields('id'),
            'SELECT `a`.`id` FROM `articles` `a`;',
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked(Table::create('articles', 'a'))
                ->fields('id', 'name'),
            'SELECT `a`.`id`, `a`.`name` FROM `articles` `a`;',
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked(Table::create('articles', 'a'))
                ->fields(Field::create('MAX(`id`)', alias: 'x', raw: true)),
            "SELECT MAX(`id`) AS 'x' FROM `articles` `a`;",
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked(Table::create('articles', 'a'))
                ->fields(Field::create('name', table: 'blub')),
            'SELECT `blub`.`name` FROM `articles` `a`;',
        ];

        yield [
            StatementBuilder::select()
                ->from(Table::create('articles'))
                ->fields(Field::create('COUNT(`id`)', raw: true)),
            'SELECT COUNT(`id`) FROM `articles`;',
        ];

        yield [
            StatementBuilder::select()
                ->distinct(false)
                ->fromLocked(Table::create('articles'))
                ->fields(Field::create('COUNT(`articles`.`id`)', raw: true)),
            'SELECT COUNT(`articles`.`id`) FROM `articles`;',
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked(Table::create('articles'))
                ->fields(Field::create('COUNT(`articles`.`id`)', raw: true)),
            'SELECT COUNT(`articles`.`id`) FROM `articles`;',
        ];

        yield [
            StatementBuilder::select()
                ->distinct()
                ->fromLocked(Table::create('articles', 'a'))
                ->fields('id', 'name')
                ->resetCurrentTable()
                ->from(Table::create('names'))
                ->fields('id'),
            'SELECT DISTINCT `a`.`id`, `a`.`name`, `id` FROM `articles` `a`, `names`;',
        ];

        yield [
            StatementBuilder::select()
                ->from('articles')
                ->fields('id')
                ->where(Comparison::of('id', ComparisonType::equal, NamedParam::create('id'))),
            'SELECT `id` FROM `articles` WHERE `id` = :id;',
        ];

        yield [
            StatementBuilder::select()
                ->from('articles')
                ->fields('id')
                ->where(Comparison::of('id', ComparisonType::equal, 15)),
            'SELECT `id` FROM `articles` WHERE `id` = 15;',
        ];

        yield [
            StatementBuilder::select()
                ->from('articles')
                ->fields('id')
                ->where(Comparison::of('name', ComparisonType::equal, 'Michael')),
            "SELECT `id` FROM `articles` WHERE `name` = 'Michael';",
        ];

        $field = Field::create('id', table: Table::create('articles'));

        yield [
            StatementBuilder::select()
                ->from('articles')
                ->fields($field)
                ->where(Comparison::of($field, ComparisonType::equal, NamedParam::create('id'))),
            'SELECT `articles`.`id` FROM `articles` WHERE `articles`.`id` = :id;',
        ];

        $field = Field::create('id', table: Table::create('articles'));

        yield [
            StatementBuilder::select()
                ->from('articles')
                ->fields($field)
                ->where(FieldComparisonParam::create($field, ComparisonType::equal, 'id')),
            'SELECT `articles`.`id` FROM `articles` WHERE `articles`.`id` = :id;',
        ];

        yield [
            StatementBuilder::select()
                ->from('articles')
                ->fields('id')
                ->where(In::create('id', [1, 2, 3])),
            'SELECT `id` FROM `articles` WHERE `id` IN (1, 2, 3);',
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->where(In::create('name', ['ab', 2])),
            "SELECT `articles`.`id` FROM `articles` WHERE `articles`.`name` IN ('ab', 2);",
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->where(In::create('id', [25, NamedParam::create('appleId')])),
            "SELECT `articles`.`id` FROM `articles` WHERE `articles`.`id` IN (25, :appleId);",
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->where(NotIn::create('name', ['ab', 2])),
            "SELECT `articles`.`id` FROM `articles` WHERE `articles`.`name` NOT IN ('ab', 2);",
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->where(Eq::of('id', 1)),
            "SELECT `articles`.`id` FROM `articles` WHERE `articles`.`id` = 1;",
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->where(Eq::of('id', 1))
                ->where(Eq::of('name', 'Adam')),
            "SELECT `articles`.`id` FROM `articles` WHERE `articles`.`id` = 1 AND `articles`.`name` = 'Adam';",
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->where(AndCondition::create(Eq::of('id', 1), Eq::of('name', 'Adam'))),
            "SELECT `articles`.`id` FROM `articles` WHERE `articles`.`id` = 1 AND `articles`.`name` = 'Adam';",
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->where(OrCondition::create(Eq::of('id', 1), Eq::of('name', 'Adam'))),
            "SELECT `articles`.`id` FROM `articles` WHERE `articles`.`id` = 1 OR `articles`.`name` = 'Adam';",
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->where(Eq::of('id', 1))
                ->where(OrCondition::create(Eq::of('id', 17), Eq::of('name', 'Adam'))),
            "SELECT `articles`.`id` FROM `articles` WHERE `articles`.`id` = 1 AND (`articles`.`id` = 17 OR `articles`.`name` = 'Adam');",
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->where(IsNotNull::of('id'))
                ->where(IsNull::of('name')),
            "SELECT `articles`.`id` FROM `articles` WHERE `articles`.`id` IS NOT NULL AND `articles`.`name` IS NULL;",
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->where(AndCondition::create()),
            "SELECT `articles`.`id` FROM `articles` WHERE 1=1;",
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->where(OrCondition::create()),
            "SELECT `articles`.`id` FROM `articles` WHERE 1=1;",
        ];

        yield [
            StatementBuilder::select()
                ->from('articles')
                ->fields('id')
                ->orderBy(Order::create('id', OrderType::ascending)),
            'SELECT `id` FROM `articles` ORDER BY `id` ASC;',
        ];

        yield [
            StatementBuilder::select()
                ->from('articles')
                ->fields('id')
                ->orderBy(
                    Order::create('name', OrderType::descending),
                    Order::create(Field::create('age', table: 'books'), OrderType::ascending),
                ),
            'SELECT `id` FROM `articles` ORDER BY `name` DESC, `books`.`age` ASC;',
        ];

        yield [
            StatementBuilder::select()
                ->from('articles')
                ->fields('id')
                ->limit(10),
            'SELECT `id` FROM `articles` LIMIT 10;',
        ];

        yield [
            StatementBuilder::select()
                ->from('articles')
                ->fields('id')
                ->limit(1)
                ->offset(20),
            'SELECT `id` FROM `articles` LIMIT 1 OFFSET 20;',
        ];

        // Joins
        yield [
            StatementBuilder::select()
                ->from('articles')
                ->fields('id')
                ->join(Join::byField(JoinType::inner, 'author_id', Field::create('id', table: 'authors'))),
            'SELECT `id` FROM `articles` INNER JOIN `authors` ON `author_id` = `authors`.`id`;',
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->join(Join::byField(JoinType::right, 'author_id', Field::create('id', table: 'authors'))),
            'SELECT `articles`.`id` FROM `articles` RIGHT JOIN `authors` ON `articles`.`author_id` = `authors`.`id`;',
        ];

        $subSelect = StatementBuilder::select()->from('authors')->fields(Field::create('id', alias: 'identifier'));

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                #->setCurrentTable('adadad')
                ->join(Join::bySubquery(
                    JoinType::inner,
                    sub: $subSelect,
                    alias: 'sub',
                    condition: Comparison::of(
                        Field::create('author_id'),
                        ComparisonType::equal,
                        Field::create('identifier', table: 'sub'),
                    ),
                )),
            "SELECT `articles`.`id` FROM `articles` INNER JOIN (SELECT `id` AS 'identifier' FROM `authors`) `sub` ON `articles`.`author_id` = `sub`.`identifier`;",
        ];

        yield [
            StatementBuilder::select()
                ->fromLocked('articles')
                ->fields('id')
                ->groupBy('name', 'id'),
            'SELECT `articles`.`id` FROM `articles` GROUP BY `articles`.`name`, `articles`.`id`;',
        ];

        yield [
            StatementBuilder::delete()
                ->fromLocked('articles')
                ->all()
            ,
            'DELETE FROM `articles`;',
        ];

        yield [
            StatementBuilder::delete()
                ->from('articles')
                ->where(
                    Comparison::of('id', ComparisonType::lessThanOrEqual, 142),
                )
            ,
            'DELETE FROM `articles` WHERE `id` <= 142;',
        ];

        yield [
            StatementBuilder::delete()
                ->fromLocked('articles')
                ->where(
                    Comparison::of('id', ComparisonType::lessThan, 151),
                )
            ,
            'DELETE FROM `articles` WHERE `articles`.`id` < 151;',
        ];

        yield [
            StatementBuilder::delete()
                ->from('articles')
                ->orderBy(Order::create('id', OrderType::descending))
                ->limit(3)
            ,
            'DELETE FROM `articles` ORDER BY `id` DESC LIMIT 3;',
        ];

        yield [
            StatementBuilder::delete()
                ->from('articles')
                ->where(OrCondition::create(
                    In::create('hour', [3, NamedParam::create('last_hour')]),
                ))
            ,
            'DELETE FROM `articles` WHERE `hour` IN (3, :last_hour);',
        ];

        /*yield [
            StatementBuilder::insert()
                ->into('articles')
                ->addRowWithFields([
                    'title' => 'John Doe - My Memories',
                ])
            ,
            'INSERT INTO `articles` (`title`) VALUES (\'John Doe - My Memories\')',
        ];

        yield [
            StatementBuilder::insert()
                ->into('articles')
                ->fields('number')
                ->addRow([
                    'number' => NamedParam::create('number'),
                    'unused_field' => 34,
                ])
            ,
            'INSERT INTO `articles` (`number`) VALUES (:number_1)',
        ];*/
    }
}