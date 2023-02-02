<?php
class IntcodeComputer
{
    public bool $halted = false;
    public bool $wantsInput = false;
    private int $ip = 0;
    private int $relativeBase = 0;

    public array $output = [];

    private array $original;
    public function __construct(private array $program, private $outputReceiver = null) {
        $this->original = $this->program;
    }
    public function run()
    {
        $methods = [
            1 => [$this, 'add'],
            2 => [$this, 'mul'],
            3 => [$this, 'getInput'],
            4 => [$this, 'sendOutput'],
            5 => [$this, 'jumpIfTrue'],
            6 => [$this, 'jumpIfFalse'],
            7 => [$this, 'lessThan'],
            8 => [$this, 'equals'],
            9 => [$this, 'baseJump'],
            99 => [$this, 'halt'],
        ];
        while (!$this->halted && isset($this->program[$this->ip])) {
            $instruction = $this->program[$this->ip];
            [$opCode, $modes] = $this->parseInstruction($instruction);
//            var_dump("op", $opCode, $modes);
            if (array_key_exists($opCode, $methods)) {
                $paused = $methods[$opCode]($modes);
                if ($paused) {
                    return;
                }
            }
//            echo implode(",", $this->program).PHP_EOL;
        }
    }

    private function baseJump(array $modes): bool
    {
        $operand = $this->program[$this->ip+1];
        $this->ip += 2;
        $this->relativeBase += $this->getValue($operand, $modes[0]);
        return false;
    }
    private function parseInstruction(int $instruction): array
    {
        $padded = str_pad((string)$instruction, 5, '0', STR_PAD_LEFT);
        $opCode = substr($padded, -2, 2);
        $modeStr = substr($padded, 0, 3);
        $modes = array_map('intval', array_reverse(str_split($modeStr)));
        return [(int)$opCode, $modes];
    }

    private function jumpIfTrue(array $modes): bool
    {
        $operands = array_slice($this->program, $this->ip+1, 2);
        if ($this->getValue($operands[0], $modes[0]) !== 0) {
            $this->ip = $this->getValue($operands[1], $modes[1]);
        } else {
            $this->ip += 3;
        }
        return false;
    }

    private function jumpIfFalse(array $modes): bool
    {
        $operands = array_slice($this->program, $this->ip+1, 2);
        if ($this->getValue($operands[0], $modes[0]) === 0) {
            $this->ip = $this->getValue($operands[1], $modes[1]);
        } else {
            $this->ip += 3;
        }
        return false;
    }

    private function lessThan(array $modes): bool
    {
        $operands = array_slice($this->program, $this->ip+1, 3);
        $v = $this->getValue($operands[0], $modes[0]) < $this->getValue($operands[1], $modes[1]) ? 1 : 0;
        $this->setValue($operands[2], $modes[2], $v);
        $this->ip += 4;
        return false;
    }

    private function equals(array $modes): bool
    {
        $operands = array_slice($this->program, $this->ip+1, 3);
        $v = $this->getValue($operands[0], $modes[0]) === $this->getValue($operands[1], $modes[1]) ? 1 : 0;
        $this->setValue($operands[2], $modes[2], $v);
        $this->ip += 4;
        return false;
    }
    private function add(array $modes): bool
    {
        $operands = array_slice($this->program, $this->ip+1, 3);
        $this->setValue($operands[2], $modes[2], $this->getValue($operands[0], $modes[0]) + $this->getValue($operands[1], $modes[1]));
        $this->ip += 4;
        return false;
    }

    private function mul(array $modes): bool
    {
        $operands = array_slice($this->program, $this->ip+1, 3);
        $this->setValue($operands[2], $modes[2], $this->getValue($operands[0], $modes[0]) * $this->getValue($operands[1], $modes[1]));
        $this->ip += 4;
        return false;
    }

    private function getInput(): bool
    {
        $this->wantsInput = true;
        return true;
    }

    private function sendOutput(array $modes): bool
    {
        $operand = $this->program[$this->ip+1];
        $this->output[] = $this->getValue($operand, $modes[0]);
        if (is_callable($this->outputReceiver)) {
            $m = $this->outputReceiver;
            $m($this->getValue($operand, $modes[0]));
        }
        $this->ip += 2;
        return false;
    }

    public function input(int $v): void
    {
        if (!$this->wantsInput) {
            throw new \Exception("Got input when not expecting it!");
        }
        $this->wantsInput = false;
        [,$modes] = $this->parseInstruction($this->program[$this->ip]);
        $operand = $this->program[$this->ip+1] ?? 0;
        $this->setValue($operand, $modes[0], $v);
        $this->program[$operand] = $v;
        $this->ip += 2;
        $this->run();
    }

    private function getValue(int $v, int $mode): int
    {
        if ($mode === 1) {
            return $v;
        }
        if ($mode === 2) {
            return $this->program[$this->relativeBase + $v] ?? 0;
        }
        return $this->program[$v] ?? 0;
    }

    private function setValue(int $i, int $mode, int $v): void
    {
        if ($mode === 2) {
            $i += $this->relativeBase;
        }
        $this->program[$i] = $v;
    }

    private function halt(): void
    {
        $this->halted = true;
    }

    public function getProgram(?int $pos = null) {
        if ($pos !== null) {
            return $this->program[$pos] ?? null;
        }
        return $this->program;
    }

    public function consumeOutput()
    {
        return array_shift($this->output);
    }

    public function reset(): void
    {
        $this->ip = 0;
        $this->program = $this->original;
        $this->halted = false;
        $this->wantsInput = false;
        $this->relativeBase = 0;
        $this->output = [];
    }
}