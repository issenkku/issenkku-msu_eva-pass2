<?php

namespace App\Services;

use App\Models\WorkloadFormField;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class WorkloadFormulaEvaluator
{
    public const SUBJECT_CREDIT_VARIABLES = [
        'credits',
        'lecture_credits',
        'lab_credits',
        'self_study_credits',
    ];

    public static function subjectCreditVariables(): array
    {
        return self::SUBJECT_CREDIT_VARIABLES;
    }

    public function evaluate(string $formula, iterable $fields, array $fieldValues): float
    {
        $normalized = $this->normalizeFormula($formula);
        $variables = $this->buildVariableMap($fields, $fieldValues);
        $tokens = $this->tokenize($normalized);
        if (empty($tokens)) {
            throw ValidationException::withMessages([
                'formula_logic' => ['สูตรการคำนวณว่างเปล่า'],
            ]);
        }
        $index = 0;
        $value = $this->parseExpression($tokens, $index, $variables);
        if ($index < count($tokens)) {
            throw ValidationException::withMessages([
                'formula_logic' => ['สูตรการคำนวณไม่ถูกต้อง'],
            ]);
        }
        if (is_bool($value)) {
            return $value ? 1.0 : 0.0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        throw ValidationException::withMessages([
            'formula_logic' => ['ผลลัพธ์สูตรการคำนวณต้องเป็นตัวเลข'],
        ]);
    }

    private function normalizeFormula(string $formula): string
    {
        $trimmed = trim($formula);
        if ($trimmed === '') {
            return $trimmed;
        }
        $trimmed = preg_replace('/(?<![A-Za-z0-9_])item_\\*(?![A-Za-z0-9_])/i', 'item_star', $trimmed);
        // If the formula contains a single "=" (not a comparison), use the left side.
        $hasComparison = str_contains($trimmed, '==') || str_contains($trimmed, '!=')
            || str_contains($trimmed, '>=') || str_contains($trimmed, '<=');
        if (! $hasComparison) {
            $pos = strpos($trimmed, '=');
            if ($pos !== false) {
                $left = trim(substr($trimmed, 0, $pos));
                if ($left !== '') {
                    return $left;
                }
            }
        }

        return $trimmed;
    }

    private function buildVariableMap(iterable $fields, array $fieldValues): array
    {
        $values = [];
        foreach ($fieldValues as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            if ($normalizedKey === 'item_*') {
                $values['item_star'] = $value;

                continue;
            }
            $values[$normalizedKey] = $value;
        }

        $variables = [];
        foreach ($fields as $field) {
            if ($field instanceof WorkloadFormField) {
                $name = strtolower((string) $field->variable_name);
                $type = strtolower((string) ($field->field_type ?? 'number'));
            } else {
                $name = strtolower((string) Arr::get($field, 'variable_name'));
                $type = strtolower((string) Arr::get($field, 'field_type', 'number'));
            }
            if ($name === '') {
                continue;
            }
            $variables[$name] = [
                'type' => $type,
                'value' => $values[$name] ?? null,
            ];
        }

        if (array_key_exists('item_star', $values) && ! array_key_exists('item_star', $variables)) {
            $variables['item_star'] = [
                'type' => 'number',
                'value' => $values['item_star'],
            ];
        }

        foreach (self::SUBJECT_CREDIT_VARIABLES as $variableName) {
            if (! array_key_exists($variableName, $values) || array_key_exists($variableName, $variables)) {
                continue;
            }

            $variables[$variableName] = [
                'type' => 'number',
                'value' => $values[$variableName],
            ];
        }

        return $variables;
    }

    private function tokenize(string $formula): array
    {
        $tokens = [];
        $length = strlen($formula);
        $i = 0;

        while ($i < $length) {
            $char = $formula[$i];

            if (ctype_space($char)) {
                $i++;

                continue;
            }

            if (ctype_digit($char) || ($char === '.' && $i + 1 < $length && ctype_digit($formula[$i + 1]))) {
                $start = $i;
                $i++;
                while ($i < $length && (ctype_digit($formula[$i]) || $formula[$i] === '.')) {
                    $i++;
                }
                $tokens[] = ['type' => 'number', 'value' => substr($formula, $start, $i - $start)];

                continue;
            }

            if (ctype_alpha($char) || $char === '_') {
                $start = $i;
                $i++;
                while ($i < $length && (ctype_alnum($formula[$i]) || $formula[$i] === '_')) {
                    $i++;
                }
                $tokens[] = ['type' => 'identifier', 'value' => substr($formula, $start, $i - $start)];

                continue;
            }

            if ($char === '(' || $char === ')' || $char === ',') {
                $tokens[] = ['type' => $char, 'value' => $char];
                $i++;

                continue;
            }

            if (in_array($char, ['+', '-', '*', '/'], true)) {
                $tokens[] = ['type' => 'operator', 'value' => $char];
                $i++;

                continue;
            }

            if (in_array($char, ['<', '>', '!', '='], true)) {
                $next = $i + 1 < $length ? $formula[$i + 1] : '';
                if ($next === '=') {
                    $tokens[] = ['type' => 'operator', 'value' => $char.$next];
                    $i += 2;

                    continue;
                }
                if ($char === '<' || $char === '>') {
                    $tokens[] = ['type' => 'operator', 'value' => $char];
                    $i++;

                    continue;
                }
            }

            throw ValidationException::withMessages([
                'formula_logic' => ["ไม่รองรับอักขระในสูตร: {$char}"],
            ]);
        }

        return $tokens;
    }

    private function parseExpression(array $tokens, int &$index, array $variables)
    {
        // Recursive descent parser: each method below represents one precedence level.
        return $this->parseOr($tokens, $index, $variables);
    }

    private function parseOr(array $tokens, int &$index, array $variables)
    {
        $value = $this->parseAnd($tokens, $index, $variables);
        while ($this->matchIdentifier($tokens, $index, ['or', 'nor', 'xor', 'xnor', 'nand'])) {
            $operator = strtolower($tokens[$index - 1]['value']);
            $right = $this->parseAnd($tokens, $index, $variables);
            $value = $this->applyLogicalOperator($operator, $value, $right);
        }

        return $value;
    }

    private function parseAnd(array $tokens, int &$index, array $variables)
    {
        $value = $this->parseComparison($tokens, $index, $variables);
        while ($this->matchIdentifier($tokens, $index, ['and'])) {
            $right = $this->parseComparison($tokens, $index, $variables);
            $value = $this->toBool($value) && $this->toBool($right);
        }

        return $value;
    }

    private function parseComparison(array $tokens, int &$index, array $variables)
    {
        $value = $this->parseAddSub($tokens, $index, $variables);
        while ($this->matchOperator($tokens, $index, ['==', '!=', '<', '<=', '>', '>='])) {
            $operator = $tokens[$index - 1]['value'];
            $right = $this->parseAddSub($tokens, $index, $variables);
            $value = $this->applyComparisonOperator($operator, $value, $right);
        }

        return $value;
    }

    private function parseAddSub(array $tokens, int &$index, array $variables)
    {
        $value = $this->parseMulDiv($tokens, $index, $variables);
        while ($this->matchOperator($tokens, $index, ['+', '-'])) {
            $operator = $tokens[$index - 1]['value'];
            $right = $this->parseMulDiv($tokens, $index, $variables);
            if ($operator === '+') {
                $value = $this->toNumber($value) + $this->toNumber($right);
            } else {
                $value = $this->toNumber($value) - $this->toNumber($right);
            }
        }

        return $value;
    }

    private function parseMulDiv(array $tokens, int &$index, array $variables)
    {
        $value = $this->parseUnary($tokens, $index, $variables);
        while ($this->matchOperator($tokens, $index, ['*', '/'])) {
            $operator = $tokens[$index - 1]['value'];
            $right = $this->parseUnary($tokens, $index, $variables);
            if ($operator === '*') {
                $value = $this->toNumber($value) * $this->toNumber($right);
            } else {
                $divisor = $this->toNumber($right);
                if ($divisor == 0.0) {
                    throw ValidationException::withMessages([
                        'formula_logic' => ['สูตรมีการหารด้วยศูนย์'],
                    ]);
                }
                $value = $this->toNumber($value) / $divisor;
            }
        }

        return $value;
    }

    private function parseUnary(array $tokens, int &$index, array $variables)
    {
        if ($this->matchOperator($tokens, $index, ['-'])) {
            $value = $this->parseUnary($tokens, $index, $variables);

            return -1 * $this->toNumber($value);
        }
        if ($this->matchIdentifier($tokens, $index, ['not'])) {
            $value = $this->parseUnary($tokens, $index, $variables);

            return ! $this->toBool($value);
        }

        return $this->parsePrimary($tokens, $index, $variables);
    }

    private function parsePrimary(array $tokens, int &$index, array $variables)
    {
        if ($this->matchTokenType($tokens, $index, 'number')) {
            return (float) $tokens[$index - 1]['value'];
        }

        if ($this->matchTokenType($tokens, $index, 'identifier')) {
            $identifier = strtolower($tokens[$index - 1]['value']);
            if ($identifier === 'true') {
                return true;
            }
            if ($identifier === 'false') {
                return false;
            }
            if ($identifier === 'if') {
                return $this->parseIfFunction($tokens, $index, $variables);
            }
            if (in_array($identifier, ['sum', 'max', 'min'], true)) {
                return $this->parseAggregateFunction($identifier, $tokens, $index, $variables);
            }

            if (! array_key_exists($identifier, $variables)) {
                throw ValidationException::withMessages([
                    'formula_logic' => ["สูตรมีตัวแปรที่ไม่รู้จัก: {$identifier}"],
                ]);
            }

            return $this->coerceVariableValue($identifier, $variables[$identifier]);
        }

        if ($this->matchTokenType($tokens, $index, '(')) {
            $value = $this->parseExpression($tokens, $index, $variables);
            if (! $this->matchTokenType($tokens, $index, ')')) {
                throw ValidationException::withMessages([
                    'formula_logic' => ['สูตรมีวงเล็บไม่ครบ'],
                ]);
            }

            return $value;
        }

        throw ValidationException::withMessages([
            'formula_logic' => ['สูตรการคำนวณไม่ถูกต้อง'],
        ]);
    }

    private function parseIfFunction(array $tokens, int &$index, array $variables)
    {
        if (! $this->matchTokenType($tokens, $index, '(')) {
            throw ValidationException::withMessages([
                'formula_logic' => ['รูปแบบ IF ไม่ถูกต้อง'],
            ]);
        }
        $condition = $this->parseExpression($tokens, $index, $variables);
        if (! $this->matchTokenType($tokens, $index, ',')) {
            throw ValidationException::withMessages([
                'formula_logic' => ['รูปแบบ IF ไม่ถูกต้อง (ต้องมี ,)'],
            ]);
        }
        $trueValue = $this->parseExpression($tokens, $index, $variables);
        if (! $this->matchTokenType($tokens, $index, ',')) {
            throw ValidationException::withMessages([
                'formula_logic' => ['รูปแบบ IF ไม่ถูกต้อง (ต้องมี ,)'],
            ]);
        }
        $falseValue = $this->parseExpression($tokens, $index, $variables);
        if (! $this->matchTokenType($tokens, $index, ')')) {
            throw ValidationException::withMessages([
                'formula_logic' => ['รูปแบบ IF ไม่ถูกต้อง (ต้องปิดวงเล็บ)'],
            ]);
        }

        return $this->toBool($condition) ? $trueValue : $falseValue;
    }

    private function parseAggregateFunction(string $function, array $tokens, int &$index, array $variables): float
    {
        if (! $this->matchTokenType($tokens, $index, '(')) {
            throw ValidationException::withMessages([
                'formula_logic' => ["รูปแบบ {$function} ไม่ถูกต้อง"],
            ]);
        }

        $values = [];
        $values[] = $this->toNumber($this->parseExpression($tokens, $index, $variables));

        while ($this->matchTokenType($tokens, $index, ',')) {
            $values[] = $this->toNumber($this->parseExpression($tokens, $index, $variables));
        }

        if (! $this->matchTokenType($tokens, $index, ')')) {
            throw ValidationException::withMessages([
                'formula_logic' => ["รูปแบบ {$function} ไม่ถูกต้อง (ต้องปิดวงเล็บ)"],
            ]);
        }

        return match ($function) {
            'sum' => array_sum($values),
            'max' => max($values),
            'min' => min($values),
        };
    }

    private function matchTokenType(array $tokens, int &$index, string $type): bool
    {
        if ($index < count($tokens) && $tokens[$index]['type'] === $type) {
            $index++;

            return true;
        }

        return false;
    }

    private function matchOperator(array $tokens, int &$index, array $operators): bool
    {
        if ($index < count($tokens) && $tokens[$index]['type'] === 'operator') {
            if (in_array($tokens[$index]['value'], $operators, true)) {
                $index++;

                return true;
            }
        }

        return false;
    }

    private function matchIdentifier(array $tokens, int &$index, array $identifiers): bool
    {
        if ($index < count($tokens) && $tokens[$index]['type'] === 'identifier') {
            $value = strtolower($tokens[$index]['value']);
            if (in_array($value, $identifiers, true)) {
                $index++;

                return true;
            }
        }

        return false;
    }

    private function coerceVariableValue(string $name, array $variable)
    {
        $type = $variable['type'] ?? 'number';
        $value = $variable['value'] ?? null;

        if ($type === 'text') {
            // Text fields are ignored in numeric calculations.
            return 0.0;
        }

        if ($value === null || $value === '') {
            throw ValidationException::withMessages([
                'field_values' => ["กรุณากรอกค่าตัวแปร: {$name}"],
            ]);
        }
        if (! is_numeric($value)) {
            throw ValidationException::withMessages([
                'field_values' => ["ค่าตัวแปรต้องเป็นตัวเลข: {$name}"],
            ]);
        }

        return (float) $value;
    }

    private function toNumber($value): float
    {
        if (is_bool($value)) {
            return $value ? 1.0 : 0.0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        throw ValidationException::withMessages([
            'formula_logic' => ['สูตรต้องใช้ค่าตัวเลขสำหรับการคำนวณ'],
        ]);
    }

    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (float) $value != 0.0;
        }
        if (is_string($value)) {
            return $value !== '';
        }

        return (bool) $value;
    }

    private function applyComparisonOperator(string $operator, $left, $right): bool
    {
        if (in_array($operator, ['==', '!='], true)) {
            $result = $left == $right;

            return $operator === '==' ? $result : ! $result;
        }

        $leftNumber = $this->toNumber($left);
        $rightNumber = $this->toNumber($right);

        return match ($operator) {
            '<' => $leftNumber < $rightNumber,
            '<=' => $leftNumber <= $rightNumber,
            '>' => $leftNumber > $rightNumber,
            '>=' => $leftNumber >= $rightNumber,
            default => false,
        };
    }

    private function applyLogicalOperator(string $operator, $left, $right): bool
    {
        $a = $this->toBool($left);
        $b = $this->toBool($right);

        return match ($operator) {
            'or' => $a || $b,
            'nor' => ! ($a || $b),
            'xor' => ($a xor $b),
            'xnor' => ! ($a xor $b),
            'nand' => ! ($a && $b),
            default => false,
        };
    }
}
