<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\rest\DataStore\ConditionBuilder;

use zaboy\rest\DataStore\ConditionBuilder\ConditionBuilderAbstract;
use Xiag\Rql\Parser\DataType\Glob;

/**
 * {@inheritdoc}
 *
 * {@inheritdoc}
 */
class PhpConditionBuilder extends ConditionBuilderAbstract
{

    protected $literals = [
        'LogicOperator' => [
            'and' => ['before' => '(', 'between' => ' && ', 'after' => ')'],
            'or' => ['before' => '(', 'between' => ' || ', 'after' => ')'],
            'not' => ['before' => '( !(', 'between' => ' error ', 'after' => ') )'],
        ],
        'ArrayOperator' => [
        ],
        'ScalarOperator' => [
            'eq' => ['before' => '(', 'between' => '==', 'after' => ')'],
            'ne' => ['before' => '(', 'between' => '!=', 'after' => ')'],
            'ge' => ['before' => '(', 'between' => '>=', 'after' => ')'],
            'gt' => ['before' => '(', 'between' => '>', 'after' => ')'],
            'le' => ['before' => '(', 'between' => '<=', 'after' => ')'],
            'lt' => ['before' => '(', 'between' => '<', 'after' => ')'],
            'like' => ['before' => '( ($_fild = ', 'between' => ") !=='' && preg_match(", 'after' => ', $_fild) )'],
        ]
    ];

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function prepareFildName($fildName)
    {
        return '$item[\'' . $fildName . '\']';
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function prepareFildValue($fildValue)
    {
        $fildValue = parent::prepareFildValue($fildValue);
        switch (true) {
            case is_bool($fildValue):
                $fildValue = (bool) $fildValue ? TRUE : FALSE;
                return $fildValue;
            case is_numeric($fildValue):
                return $fildValue;
            case is_string($fildValue):
                return "'" . $fildValue . "'";
            default:
                throw new DataStoresException(
                'Type ' . gettype($fildValue) . ' is not supported'
                );
        }
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getValueFromGlob(Glob $globNode)
    {
        $constStar = 'star_hjc7vjHg6jd8mv8hcy75GFt0c67cnbv74FegxtEDJkcucG64frblmkb';
        $constQuestion = 'question_hjc7vjHg6jd8mv8hcy75GFt0c67cnbv74FegxtEDJkcucG64frblmkb';

        $glob = parent::getValueFromGlob($globNode);
        $anchorStart = true;
        if (substr($glob, 0, 1) === '*') {
            $anchorStart = false;
            $glob = ltrim($glob, '*');
        }
        $anchorEnd = true;
        if (substr($glob, -1) === '*') {
            $anchorEnd = false;
            $glob = rtrim($glob, '*');
        }
        $regex = strtr(
                preg_quote(rawurldecode(strtr($glob, ['*' => $constStar, '?' => $constQuestion])), '/'), [$constStar => '.*', $constQuestion => '.']
        );
        if ($anchorStart) {
            $regex = '^' . $regex;
        }
        if ($anchorEnd) {
            $regex = $regex . '$';
        }
        return '/' . $regex . '/i';
    }

}
